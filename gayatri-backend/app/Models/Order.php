<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['client_id', 'order_type', 'status', 'subtotal', 'gst', 'total', 'payment_status', 'payment_mode'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'gst' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function deliveryChallan()
    {
        return $this->hasOne(DeliveryChallan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
