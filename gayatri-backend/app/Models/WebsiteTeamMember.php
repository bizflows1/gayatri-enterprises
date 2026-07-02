<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteTeamMember extends Model
{
    use HasFactory;

    protected $table = 'website_team_members';

    protected $fillable = [
        'name',
        'role',
        'qualification',
        'bio',
        'image_path',
        'display_order',
        'category',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
        'display_order' => 'integer'
    ];
}
