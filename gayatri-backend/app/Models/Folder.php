<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'parent_id', 'path'];

    // Folder belongs to a User (Client)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A Folder can have many sub-folders
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    // A Folder belongs to a parent Folder
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    // A Folder has many Documents
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
