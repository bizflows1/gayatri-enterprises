<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message_id;
    public $user_id;

    public function __construct($message_id, $user_id)
    {
        $this->message_id = $message_id;
        $this->user_id = $user_id;
    }

    public function broadcastOn(): array
    {
        // Broadcast on a private channel for the specific conversation
        $message = \App\Models\Message::find($this->message_id);
        return [
            new PrivateChannel('chat.' . ($message ? $message->conversation_id : 0)),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message_id,
            'user_id' => $this->user_id,
            'read_at' => now()->toIso8601String(),
        ];
    }
}
