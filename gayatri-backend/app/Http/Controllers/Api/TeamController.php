<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebsiteTeamMember;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index()
    {
        $members = WebsiteTeamMember::orderBy('display_order')->get()->map(fn(WebsiteTeamMember $m) => [
            'id'            => $m->id,
            'name'          => $m->name,
            'role'          => $m->role,
            'bio'           => $m->bio,
            'qualification' => $m->qualification,
            'image'         => $m->image_path
                ? (str_starts_with($m->image_path, '/') ? $m->image_path : Storage::disk('public')->url($m->image_path))
                : null,
            'category'      => $m->category,
        ]);

        return response()->json(['members' => $members]);
    }
}
