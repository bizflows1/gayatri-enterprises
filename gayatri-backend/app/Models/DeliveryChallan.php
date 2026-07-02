<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChallan extends Model
{
    protected $fillable = ['order_id', 'challan_no', 'pdf_path'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
