<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::approved()
            ->orderByDesc('created_at')
            ->get(['name', 'designation', 'rating', 'body']);

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'rating'      => 'required|integer|min:1|max:5',
            'body'        => 'required|string|max:2000',
        ]);

        Review::create($data); // status defaults to 'pending'

        return response()->json(['message' => 'Review submitted for approval. Thank you!'], 201);
    }
}
