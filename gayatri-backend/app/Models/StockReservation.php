<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    protected $fillable = ['batch_id', 'order_id', 'qty', 'status'];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
