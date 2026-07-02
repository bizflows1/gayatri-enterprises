<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    use HasFactory;

    protected $table = 'message_reads'; // Fallback
    public $timestamps = false;          // production table has no timestamp columns
    protected $fillable = ['message_id', 'user_id'];

    protected static $resolvedTable = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (self::$resolvedTable === null) {
            try {
                self::$resolvedTable = \Schema::hasTable('team_message_reads') ? 'team_message_reads' : 'message_reads';
            } catch (\Exception $e) {
                self::$resolvedTable = 'message_reads';
            }
        }
        $this->table = self::$resolvedTable;
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function reader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
