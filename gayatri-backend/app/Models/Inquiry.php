<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'source', 'name', 'email', 'institution', 'type', 'message',
        'company', 'industry', 'contact_person', 'requirements', 'needs_msds_coa',
        'status', 'notes',
    ];

    protected $casts = [
        'needs_msds_coa' => 'boolean',
    ];
}
