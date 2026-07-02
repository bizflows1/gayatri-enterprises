<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    // Client accounts authenticate against the public React site via the
    // Sanctum API, not this panel — only staff/admin get into /admin.
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isStaff();
    }

    protected $fillable = [
        'name',
        'role',
        'phone',
        'email',
        'password',
        'permissions',
        'gst_number',
        'pan_number',
        'login_attempts',
        'last_seen_at',
        'profile_photo',
        'storage_name',
        'date_of_birth',
        'anniversary_date',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'upi_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $appends = ['avatar_url'];

    // Relationship with documents
    public function documents()
    {
        return $this->hasMany(\App\Models\Document::class);
    }

    public function client()
    {
        return $this->hasOne(\App\Models\Client::class);
    }

    // Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Check if user is staff
    // Portal-wide Avatar Fallback
    public function getAvatarUrlAttribute()
    {
        if ($this->profile_photo && trim($this->profile_photo) !== '') {
            return url('/chat/file/' . $this->profile_photo);
        }
        $name = urlencode($this->name);
        
        // Dynamically match the chat sender name colors
        $colors = ['4f46e5', '0d9488', 'ea580c', 'db2777', '0284c7'];
        $bg = $colors[$this->id % 5];
        
        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff&bold=true";
    }

    // Check if user is staff
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    // Check if user is client
    public function isClient()
    {
        return $this->role === 'client';
    }
    // Helper to check permissions
    public function hasPermission($permission) {
        // 1. Admin ke paas sab powers hain
        if ($this->role === 'admin') {
            return true;
        }

        // 2. Client ke paas koi power nahi (sirf apna dashboard)
        if ($this->role === 'client') {
            return false;
        }

        // 3. Staff: Permissions JSON decode karke check karo
        $perms = json_decode($this->permissions, true) ?? [];
        return in_array($permission, $perms);
    }
    public function tasks() {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function notices()
    {
        return $this->belongsToMany(SiteNotice::class, 'notice_user')
                    ->withPivot('is_read', 'read_at')
                    ->withTimestamps();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)->withPivot('last_read_at')->withTimestamps();
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }
}
