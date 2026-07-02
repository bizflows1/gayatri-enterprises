<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxEmail extends Model
{
    protected $table = 'mailbox_emails';

    protected $fillable = [
        'account_key',
        'folder_name',
        'uid',
        'subject',
        'from_name',
        'from_raw',
        'reply_to',
        'date_string',
        'imap_timestamp',
        'seen',
        'starred',
        'body',
        'summary',
    ];

    protected $casts = [
        'seen' => 'boolean',
        'starred' => 'boolean',
        'imap_timestamp' => 'integer',
    ];
}
