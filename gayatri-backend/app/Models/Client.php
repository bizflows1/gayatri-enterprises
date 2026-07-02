<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'gstin', 'credit_limit',
        'outstanding_balance', 'price_tier', 'assigned_staff_id', 'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creditAvailable(): float
    {
        return (float) $this->credit_limit - (float) $this->outstanding_balance;
    }
}
