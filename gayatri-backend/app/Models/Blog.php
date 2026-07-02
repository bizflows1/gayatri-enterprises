<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'author',
        'content',
        'excerpt',
        'image_url',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
