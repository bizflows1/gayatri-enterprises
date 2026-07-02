<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationship with User (Employee)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Approver (Admin)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
