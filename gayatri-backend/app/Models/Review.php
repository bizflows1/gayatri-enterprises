<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['name', 'designation', 'rating', 'body', 'status'];

    // Scope to get only approved reviews
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper: get initials from name
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', trim($this->name));
        return strtoupper(
            (isset($parts[0]) ? $parts[0][0] : '') .
            (isset($parts[1]) ? $parts[1][0] : '')
        );
    }

    // Helper: avatar background color based on name
    public function getAvatarColorAttribute(): string
    {
        $colors = ['bg-blue-600', 'bg-indigo-600', 'bg-teal-600', 'bg-slate-700', 'bg-purple-600', 'bg-rose-600'];
        return $colors[crc32($this->name) % count($colors)];
    }
}
