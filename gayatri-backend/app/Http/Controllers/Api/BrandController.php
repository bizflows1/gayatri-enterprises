<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('name')->get()->map(fn(Brand $b) => [
            'id'   => $b->id,
            'name' => $b->name,
            'logo' => $b->logo
                ? (str_starts_with($b->logo, '/') ? $b->logo : Storage::disk('public')->url($b->logo))
                : null,
        ]);

        return response()->json(['brands' => $brands]);
    }
}
