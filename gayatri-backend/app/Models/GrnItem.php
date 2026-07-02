<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $table = 'grn_items';

    protected $fillable = ['grn_id', 'product_id', 'batch_no', 'expiry_date', 'qty', 'purchase_price', 'batch_id'];

    protected $casts = [
        'expiry_date' => 'date',
        'qty' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'grn_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
