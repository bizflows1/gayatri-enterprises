<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id', 'batch_id', 'type', 'qty_signed',
        'reason', 'ref_type', 'ref_id', 'created_by',
    ];

    protected $casts = [
        'qty_signed' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'ref_type', 'ref_id');
    }
}
