<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'team_messages';
    protected $fillable = [
        'conversation_id', 
        'user_id', 
        'body', 
        'attachment', 
        'parent_id', 
        'is_deleted_globally', 
        'deleted_by', 
        'starred_by', 
        'pinned_by'
    ];

    protected $casts = [
        'deleted_by' => 'array',
        'starred_by' => 'array',
        'pinned_by' => 'array',
        'is_deleted_globally' => 'boolean',
    ];

    // Who sent it
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Which conversation it belongs to
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Quoted message
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    // Read receipts
    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }
}
