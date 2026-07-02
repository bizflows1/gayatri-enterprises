<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_hours',
        'overtime_hours',
        'auto_logged_out',
        'status',
        'clock_in_ip',
        'clock_out_ip',
        'latitude',
        'longitude',
        'selfie_photo',
        'notes',
        'admin_remarks',
        'work_log',
        'lunch_start',
        'lunch_end',
        'lunch_duration'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'auto_logged_out' => 'boolean',
        'lunch_start' => 'datetime',
        'lunch_end' => 'datetime',
        'lunch_duration' => 'decimal:2',
    ];

    protected $appends = ['selfie_url', 'formatted_worked_hours', 'formatted_overtime_hours'];

    // Accessor for Formatted Worked Hours (e.g. 8 hrs 30 min)
    public function getFormattedWorkedHoursAttribute()
    {
        if (!$this->clock_out || !$this->total_hours || floatval($this->total_hours) <= 0) {
            return '-';
        }
        
        $totalMinutes = round(floatval($this->total_hours) * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours} hrs {$minutes} min";
        } elseif ($hours > 0) {
            return "{$hours} hrs";
        } else {
            return "{$minutes} min";
        }
    }

    // Accessor for Formatted Overtime Hours (e.g. 2 hrs 15 min)
    public function getFormattedOvertimeHoursAttribute()
    {
        if (!$this->overtime_hours || floatval($this->overtime_hours) <= 0) {
            return '-';
        }
        
        $totalMinutes = round(floatval($this->overtime_hours) * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours} hrs {$minutes} min";
        } elseif ($hours > 0) {
            return "{$hours} hrs";
        } else {
            return "{$minutes} min";
        }
    }

    // Relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for Selfie Photo URL
    public function getSelfieUrlAttribute()
    {
        if ($this->selfie_photo && trim($this->selfie_photo) !== '') {
            // Secure direct fallback bypasses Hostinger symlink issues
            return url('/chat/file/' . $this->selfie_photo);
        }
        return null;
    }

    // Scope for active/today records
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}
