<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['task_id', 'user_id', 'message', 'attachment', 'is_read'];

    // Message bhejne wala user kaun hai?
    public function user() {
        return $this->belongsTo(User::class);
    }
}