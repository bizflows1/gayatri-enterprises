<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Handle lead submissions from GS checklist and callback forms.
     */
    public function submit(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'type' => 'required|string|in:gst_checklist,callback',
            'mode' => 'nullable|string|max:50', // Optional field from callback form
        ]);

        $leadData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'type' => $request->type,
            'meta' => $request->mode ? ['mode' => $request->mode] : null,
        ];

        // 2. Save to Database (Local backup)
        // We use a try-catch for DB because local environment might hang (Hostinger settings)
        try {
            Lead::create($leadData);

            // Send Push Notification to admins & staff
            $recipients = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
            $typeLabel = $leadData['type'] === 'gst_checklist' ? 'GST Checklist' : 'Callback Request';
            $modeLabel = $request->mode ? " ({$request->mode})" : "";
            \App\Services\PushNotificationService::sendToUsers(
                $recipients,
                "New Lead: " . $typeLabel . $modeLabel,
                "Name: {$leadData['name']} | Phone: {$leadData['phone']}",
                url('/admin/dashboard')
            );
        } catch (\Exception $e) {
            Log::warning('Lead DB storage / Push Notification failed: ' . $e->getMessage());
        }

        // 3. Forward to Google Sheets Web App
        $sheetsUrl = env('GOOGLE_SHEET_WEBAPP_URL');
        
        if ($sheetsUrl) {
            try {
                Http::timeout(5)->post($sheetsUrl, [
                    'name' => $leadData['name'],
                    'phone' => $leadData['phone'],
                    'type' => $leadData['type'],
                    'mode' => $request->mode ?? 'N/A',
                ]);
            } catch (\Exception $e) {
                Log::error('Google Sheets Forwarding failed: ' . $e->getMessage());
            }
        } else {
            Log::info('Google Sheet URL not configured in .env');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Your request has been received. We will contact you shortly.'
        ]);
    }
}
