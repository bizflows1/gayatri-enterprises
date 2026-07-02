<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\GoodsReceipt;
use App\Models\GrnItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Inventory core: GRN intake, FEFO allocation, returns, and manual adjustment.
 * Every method that mutates qty_remaining runs inside a locked transaction —
 * this is the only place those numbers are allowed to move. Callers (order
 * confirmation, returns processing, reconciliation) wrap their own business
 * logic (credit checks, ledger postings) around these calls, in the same
 * transaction, so a failure anywhere rolls back the whole operation.
 */
class StockService
{
    /**
     * GRN -> stock IN. For each grn_item: create a batch row (qty_received =
     * qty_remaining = qty), backfill grn_items.batch_id, and log an immutable
     * "in" stock_movement. Architecture doc §4.2.
     */
    public function receiveGoodsReceipt(GoodsReceipt $grn, ?int $createdBy = null): array
    {
        return DB::transaction(function () use ($grn, $createdBy) {
            $batches = [];

            foreach ($grn->items()->whereNull('batch_id')->get() as $item) {
                /** @var GrnItem $item */
                $batch = Batch::create([
                    'product_id' => $item->product_id,
                    'batch_no' => $item->batch_no,
                    'expiry_date' => $item->expiry_date,
                    'purchase_price' => $item->purchase_price,
                    'qty_received' => $item->qty,
                    'qty_remaining' => $item->qty,
                    'supplier_id' => $grn->supplier_id,
                    'grn_id' => $grn->id,
                    'condition' => 'good',
                    'received_at' => $grn->received_at,
                ]);

                $item->update(['batch_id' => $batch->id]);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'in',
                    'qty_signed' => $item->qty,
                    'ref_type' => GoodsReceipt::class,
                    'ref_id' => $grn->id,
                    'created_by' => $createdBy,
                ]);

                $batches[] = $batch;
            }

            return $batches;
        });
    }

    /**
     * FEFO allocation + decrement for a single product line. Locks every
     * candidate batch (good, non-expired, qty_remaining > 0) ordered by
     * earliest expiry first, aborts the whole transaction if total available
     * is short, otherwise decrements across batches and logs an "out"
     * movement per batch touched. Returns [['batch_id' => int, 'qty' => float], ...]
     * so the caller (order confirmation) can persist order_allocations itself.
     *
     * Architecture doc §4.1. Caller is expected to already be inside — or to
     * wrap this call in — a DB::transaction() alongside their own credit-limit
     * gate and ledger posting.
     */
    public function allocateFefo(int $productId, float $qty, string $refType, int $refId, ?int $createdBy = null): array
    {
        return DB::transaction(function () use ($productId, $qty, $refType, $refId, $createdBy) {
            $batches = Batch::where('product_id', $productId)
                ->where('condition', 'good')
                ->where('expiry_date', '>', now())
                ->where('qty_remaining', '>', 0)
                ->orderBy('expiry_date')
                ->lockForUpdate()
                ->get();

            if ($batches->sum('qty_remaining') < $qty) {
                throw new InsufficientStockException("Insufficient stock for product {$productId}: requested {$qty}, available {$batches->sum('qty_remaining')}");
            }

            $remaining = $qty;
            $allocations = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (float) $batch->qty_remaining);
                $batch->decrement('qty_remaining', $take);

                StockMovement::create([
                    'product_id' => $productId,
                    'batch_id' => $batch->id,
                    'type' => 'out',
                    'qty_signed' => -$take,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                    'created_by' => $createdBy,
                ]);

                $allocations[] = ['batch_id' => $batch->id, 'qty' => $take];
                $remaining -= $take;
            }

            return $allocations;
        });
    }

    /**
     * Returns processing. Targets the ORIGINAL batch (preserves its expiry)
     * rather than creating a new one. Good condition -> back into sellable
     * qty_remaining. Damaged/expired -> quarantined, a separate batch row so
     * the original batch's history stays clean, with qty_remaining = the
     * returned qty and condition set accordingly (never sellable as 'good').
     * Architecture doc §4.3. Ledger credit-note posting is the caller's job.
     */
    public function returnToBatch(Batch $originalBatch, float $qty, string $condition, ?string $reason, int $refId, ?int $createdBy = null): Batch
    {
        return DB::transaction(function () use ($originalBatch, $qty, $condition, $reason, $refId, $createdBy) {
            if ($condition === 'good') {
                $originalBatch->lockForUpdate();
                $originalBatch->increment('qty_remaining', $qty);
                $target = $originalBatch;
            } else {
                $target = Batch::create([
                    'product_id' => $originalBatch->product_id,
                    'batch_no' => $originalBatch->batch_no,
                    'expiry_date' => $originalBatch->expiry_date,
                    'purchase_price' => $originalBatch->purchase_price,
                    'qty_received' => $qty,
                    'qty_remaining' => $qty,
                    'supplier_id' => $originalBatch->supplier_id,
                    'grn_id' => $originalBatch->grn_id,
                    'condition' => $condition,
                    'received_at' => now(),
                ]);
            }

            StockMovement::create([
                'product_id' => $originalBatch->product_id,
                'batch_id' => $target->id,
                'type' => 'return',
                'qty_signed' => $qty,
                'reason' => $reason,
                'ref_type' => 'return',
                'ref_id' => $refId,
                'created_by' => $createdBy,
            ]);

            return $target;
        });
    }

    /**
     * Physical-count reconciliation. Logs the diff as an "adjust" movement
     * with a mandatory reason — the only sanctioned way to fix drift between
     * qty_remaining and what's actually on the shelf. Architecture doc §4.6.
     */
    public function adjustStock(Batch $batch, float $actualQty, string $reason, ?int $createdBy = null): Batch
    {
        return DB::transaction(function () use ($batch, $actualQty, $reason, $createdBy) {
            $batch = Batch::where('id', $batch->id)->lockForUpdate()->first();
            $diff = $actualQty - (float) $batch->qty_remaining;

            if ($diff === 0.0) {
                return $batch;
            }

            $batch->update(['qty_remaining' => $actualQty]);

            StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'type' => 'adjust',
                'qty_signed' => $diff,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            return $batch;
        });
    }
}
