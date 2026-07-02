<?php

use Illuminate\Support\Facades\Broadcast;

if (class_exists('Pusher\Pusher')) {
    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
        return \App\Models\Conversation::where('id', $conversationId)
            ->whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->exists();
    });

    Broadcast::channel('chat.presence', function ($user) {
        if ($user->role !== 'client') {
            return ['id' => $user->id, 'name' => $user->name];
        }
    });
}
