<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'qty', 'unit_price', 'negotiated_price'];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'negotiated_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function allocations()
    {
        return $this->hasMany(OrderAllocation::class);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->negotiated_price ?? $this->unit_price);
    }

    public function lineTotal(): float
    {
        return $this->effectivePrice() * (float) $this->qty;
    }
}
