<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'product_id', 'batch_no', 'expiry_date', 'purchase_price',
        'qty_received', 'qty_remaining', 'supplier_id', 'grn_id',
        'condition', 'received_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'received_at' => 'datetime',
        'purchase_price' => 'decimal:2',
        'qty_received' => 'decimal:2',
        'qty_remaining' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'grn_id');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reservations()
    {
        return $this->hasMany(StockReservation::class);
    }

    public function isSellable(): bool
    {
        return $this->condition === 'good' && $this->expiry_date->isFuture() && $this->qty_remaining > 0;
    }
}
