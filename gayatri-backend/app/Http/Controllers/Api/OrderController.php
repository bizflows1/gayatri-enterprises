<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CreditLimitExceededException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Client-portal ordering. All routes here sit behind auth:sanctum — every
 * order is scoped to the authenticated user's own client record, never a
 * client_id taken from the request body.
 */
class OrderController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $client = $request->user()->client;

        $orders = $client->orders()
            ->with(['items.product', 'invoice'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOwnership($request, $order);

        return response()->json($order->load(['items.product', 'items.allocations', 'invoice']));
    }

    public function store(Request $request)
    {
        $client = $request->user()->client;

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,cheque,neft',
        ]);

        $order = DB::transaction(function () use ($client, $data) {
            $order = Order::create([
                'client_id' => $client->id,
                'order_type' => 'portal',
                'status' => 'draft',
                'payment_mode' => $data['payment_mode'],
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $line) {
                $product = Product::findOrFail($line['product_id']);
                $unitPrice = (float) ($product->sales_price ?? 0);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $line['qty'],
                    'unit_price' => $unitPrice,
                ]);

                $subtotal += $unitPrice * (float) $line['qty'];
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            return $order;
        });

        return response()->json($order->load('items.product'), 201);
    }

    public function confirm(Request $request, Order $order)
    {
        $this->authorizeOwnership($request, $order);

        if ($order->status !== 'draft') {
            return response()->json(['message' => 'Only draft orders can be confirmed.'], 422);
        }

        try {
            $confirmed = $this->orders->confirm($order->load('items.product'), $request->user()->id);
        } catch (CreditLimitExceededException $e) {
            Log::info($e->getMessage());
            return response()->json([
                'message' => 'This order exceeds your approved credit limit. Our sales team has been notified and will reach out to get your account set up — or contact us directly to place this order.',
            ], 422);
        } catch (InsufficientStockException $e) {
            Log::warning($e->getMessage());
            return response()->json([
                'message' => 'One or more items in this order just went out of stock. Please adjust the quantities and try again.',
            ], 422);
        }

        return response()->json($confirmed->load(['items.allocations', 'invoice']));
    }

    private function authorizeOwnership(Request $request, Order $order): void
    {
        abort_unless($order->client_id === $request->user()->client?->id, 403);
    }
}
