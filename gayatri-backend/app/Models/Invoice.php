<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'invoice_no', 'gst_breakup_json', 'hsn_summary_json', 'pdf_path'];

    protected $casts = [
        'gst_breakup_json' => 'array',
        'hsn_summary_json' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
