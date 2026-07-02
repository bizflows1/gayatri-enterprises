<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'cas_number', 'brand_id', 'category_id',
        'hsn_code', 'grade', 'pack_size', 'unit', 'sales_price',
        'description', 'sds_file', 'coa_file', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sales_price' => 'decimal:2',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Sellable quantity: good condition, non-expired, remaining > 0.
     * Mirrors the FEFO query StockService uses, kept here for read-only display.
     */
    public function availableQty(): float
    {
        return (float) $this->batches()
            ->where('condition', 'good')
            ->where('expiry_date', '>', now())
            ->sum('qty_remaining');
    }

    /** Total inventory value = SUM(qty_remaining × purchase_price) across sellable batches. */
    public function inventoryValue(): float
    {
        return (float) ($this->batches()
            ->where('condition', 'good')
            ->where('expiry_date', '>', now())
            ->selectRaw('SUM(qty_remaining * purchase_price) as value')
            ->value('value') ?? 0.0);
    }

    /** Weighted average purchase price across sellable batches. */
    public function avgPurchasePrice(): ?float
    {
        $batches = $this->batches()
            ->where('condition', 'good')
            ->where('expiry_date', '>', now())
            ->where('qty_remaining', '>', 0)
            ->selectRaw('SUM(qty_remaining) as total_qty, SUM(qty_remaining * purchase_price) as total_value')
            ->first();

        if (!$batches || (float) $batches->total_qty === 0.0) {
            return null;
        }

        return (float) $batches->total_value / (float) $batches->total_qty;
    }
}
