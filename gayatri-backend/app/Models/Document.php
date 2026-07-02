<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    // Ye fields hum save kar sakte hain
    protected $fillable = [
        'user_id',
        'filename',
        'file_path',
        'category',
        'folder_id',
    ];

    // Relationship: Har document kisi ek User ka hota hai
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}