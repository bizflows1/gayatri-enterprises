<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'type',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];
}
