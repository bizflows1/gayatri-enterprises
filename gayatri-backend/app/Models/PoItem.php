<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    protected $table = 'po_items';

    protected $fillable = ['po_id', 'product_id', 'qty', 'purchase_price'];

    protected $casts = [
        'qty' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
