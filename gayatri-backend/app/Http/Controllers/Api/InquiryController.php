<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $source = $request->input('source', 'contact');

        if ($source === 'bulk_order') {
            $data = $request->validate([
                'name'          => 'required|string|max:255',
                'email'         => 'required|email|max:255',
                'company'       => 'required|string|max:255',
                'industry'      => 'required|string|max:100',
                'contact_person'=> 'required|string|max:255',
                'requirements'  => 'required|string',
                'needs_msds_coa'=> 'boolean',
            ]);
            $data['source'] = 'bulk_order';
        } else {
            $data = $request->validate([
                'name'       => 'required|string|max:255',
                'email'      => 'required|email|max:255',
                'institution'=> 'nullable|string|max:255',
                'type'       => 'nullable|string|max:100',
                'message'    => 'required|string',
            ]);
            $data['source'] = 'contact';
        }

        $inquiry = Inquiry::create($data);

        return response()->json(['message' => 'Inquiry received.', 'id' => $inquiry->id], 201);
    }
}
