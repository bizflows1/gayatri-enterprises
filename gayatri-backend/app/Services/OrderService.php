<?php

namespace App\Services;

use App\Exceptions\CreditLimitExceededException;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderAllocation;
use Illuminate\Support\Facades\DB;

/**
 * Order confirmation: the module that earns/loses trust (architecture.md §4.1).
 * One DB transaction, row locks throughout. Credit-limit gate, then FEFO
 * allocation per line via StockService (which takes its own locks on the
 * candidate batches), then invoice generation, then ledger posting. Any
 * failure anywhere — short stock, credit exceeded — rolls back everything,
 * including whatever StockService already decremented in this same request.
 */
class OrderService
{
    public function __construct(
        private StockService $stock,
        private LedgerService $ledger,
        private InvoiceService $invoices,
    ) {
    }

    public function confirm(Order $order, ?int $confirmedBy = null): Order
    {
        return DB::transaction(function () use ($order, $confirmedBy) {
            $client = Client::where('id', $order->client_id)->lockForUpdate()->first();

            if ((float) $client->outstanding_balance + (float) $order->total > (float) $client->credit_limit) {
                throw new CreditLimitExceededException(
                    "Credit limit exceeded for client {$client->id}: outstanding {$client->outstanding_balance} + order {$order->total} > limit {$client->credit_limit}"
                );
            }

            foreach ($order->items as $item) {
                $allocations = $this->stock->allocateFefo(
                    $item->product_id,
                    (float) $item->qty,
                    Order::class,
                    $order->id,
                    $confirmedBy
                );

                foreach ($allocations as $allocation) {
                    OrderAllocation::create([
                        'order_item_id' => $item->id,
                        'batch_id' => $allocation['batch_id'],
                        'qty' => $allocation['qty'],
                    ]);
                }
            }

            $invoice = $this->invoices->generate($order);
            $this->ledger->post($client, 'invoice', (float) $order->total, get_class($invoice), $invoice->id);

            $order->update(['status' => 'confirmed']);

            return $order->refresh();
        });
    }
}
