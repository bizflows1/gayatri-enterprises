<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Yeh batata hai ki kaun-kaun se columns mein data save kiya ja sakta hai
    protected $fillable = ['title', 'description', 'priority', 'due_date', 'status'];

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }

    // Checking if a user is assigned
    public function isAssignedTo($userId) {
        return $this->assignees()->where('user_id', $userId)->exists();
    }

    public function chats() {
        return $this->hasMany(Chat::class);
    }
}