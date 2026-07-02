<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['client_id', 'type', 'amount_signed', 'ref_type', 'ref_id', 'balance_after'];

    protected $casts = [
        'amount_signed' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
