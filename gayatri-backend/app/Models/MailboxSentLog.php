<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxSentLog extends Model
{
    protected $table = 'mailbox_sent_logs';

    protected $fillable = [
        'user_id',
        'account_key',
        'to',
        'subject',
        'body',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
