<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MailboxAccount extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'address',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
    ];

    /** Securely encrypt password before storing in DB */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /** Decrypt password automatically when fetched */
    public function getDecryptedPassword()
    {
        return Crypt::decryptString($this->password);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
