<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    /**
     * Handle incoming Razorpay Webhooks.
     * Required for "Silent Failure" recovery (User closes tab before callback).
     */
    public function handle(Request $request)
    {
        $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');
        $signature = $request->header('X-Razorpay-Signature');

        if (empty($webhookSecret)) {
            Log::critical('[Razorpay Webhook] Webhook secret is not configured in .env. Denying request.');
            return response()->json(['status' => 'error', 'message' => 'Webhook is not configured.'], 500);
        }

        if (!$signature) {
            Log::warning('[Razorpay Webhook] Missing Signature');
            return response()->json(['status' => 'error', 'message' => 'Missing signature'], 400);
        }

        // Verify Signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::error('[Razorpay Webhook] Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? '';

        Log::info('[Razorpay Webhook] Received Event: ' . $event);

        if ($event === 'payment.captured') {
            $payment = $data['payload']['payment']['entity'];
            $razorpayOrderId = $payment['order_id'];
            $razorpayPaymentId = $payment['id'];

            // The pending Payment row (reference = razorpay_order_id) is created
            // when checkout is initiated — that endpoint belongs to the client
            // portal's checkout flow, not yet built. Until then this just logs
            // and acks; once checkout exists, every webhook hit will find a row.
            $record = Payment::where('reference', $razorpayOrderId)->first();

            if (! $record) {
                Log::warning('[Razorpay Webhook] No pending Payment found for razorpay_order_id: ' . $razorpayOrderId);
                return response()->json(['status' => 'success', 'message' => 'No matching payment record']);
            }

            if ($record->status === 'success') {
                Log::info('[Razorpay Webhook] Payment already marked success for razorpay_order_id: ' . $razorpayOrderId);
                return response()->json(['status' => 'success', 'message' => 'Already processed']);
            }

            try {
                DB::transaction(function () use ($record, $razorpayPaymentId) {
                    $record->update(['status' => 'success', 'reference' => $razorpayPaymentId]);

                    app(LedgerService::class)->post(
                        $record->client,
                        'payment',
                        (float) $record->amount,
                        Payment::class,
                        $record->id
                    );

                    if ($record->order) {
                        $record->order->update(['payment_status' => 'paid']);
                    }
                });

                Log::info('[Razorpay Webhook] Payment confirmed and posted to ledger', ['payment_id' => $record->id]);
            } catch (\Exception $e) {
                Log::error('[Razorpay Webhook] Fulfillment failed: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Fulfillment failed'], 500);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
