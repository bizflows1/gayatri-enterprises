<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Store a new public review (submitted by visitor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'designation' => 'nullable|string|max:150',
            'rating'      => 'required|integer|min:1|max:5',
            'body'        => 'required|string|min:20|max:1000',
        ]);

        Review::create([
            'name'        => strip_tags($request->name),
            'designation' => strip_tags($request->designation ?? ''),
            'rating'      => $request->rating,
            'body'        => strip_tags($request->body),
            'status'      => 'pending',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Thank you for your review! It will appear after moderation.',
        ]);
    }

    /**
     * Admin: list all reviews (pending + approved)
     */
    public function adminIndex()
    {
        $reviews = Review::latest()->get();
        return view('admin.reviews', compact('reviews'));
    }

    /**
     * Admin: approve a review
     */
    public function approve($id)
    {
        Review::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', 'Review approved.');
    }

    /**
     * Admin: reject a review
     */
    public function reject($id)
    {
        Review::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Review rejected.');
    }

    /**
     * Admin: delete a review
     */
    public function destroy($id)
    {
        Review::findOrFail($id)->delete();
        return back()->with('success', 'Review deleted.');
    }
}
