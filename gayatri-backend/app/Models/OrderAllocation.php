<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAllocation extends Model
{
    protected $fillable = ['order_item_id', 'batch_id', 'qty'];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
