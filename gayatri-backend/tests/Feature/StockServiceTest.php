<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\GoodsReceipt;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Correctness tests for the inventory core. These run against the default
 * sqlite in-memory test DB and a single PHP process, so they prove the FEFO
 * sequencing/decrement/exception logic is right — they do NOT prove the
 * MySQL row-locking holds up under real concurrent requests (sqlite has no
 * meaningful row-level locking, and PHPUnit runs sequentially anyway).
 * Once the order-confirm HTTP endpoint exists, verify concurrent correctness
 * with a real load tool (k6/artillery firing parallel requests at a
 * MySQL-backed instance) per architecture.md's "stress-test before any
 * portal ordering" instruction.
 */
class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stock;
    private Product $product;
    private Supplier $supplier;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stock = new StockService();
        $this->user = User::factory()->create();
        $this->supplier = Supplier::create(['name' => 'Test Supplier']);
        $this->product = Product::create([
            'name' => 'Test Acetone', 'slug' => 'test-acetone', 'cas_number' => '67-64-1',
            'pack_size' => '500ml', 'unit' => 'bottle', 'is_active' => true,
        ]);
    }

    private function receive(string $batchNo, string $expiryDate, float $qty): Batch
    {
        $grn = GoodsReceipt::create([
            'supplier_id' => $this->supplier->id,
            'received_at' => now(),
            'received_by' => $this->user->id,
        ]);

        GrnItem::create([
            'grn_id' => $grn->id,
            'product_id' => $this->product->id,
            'batch_no' => $batchNo,
            'expiry_date' => $expiryDate,
            'qty' => $qty,
            'purchase_price' => 100,
        ]);

        $batches = $this->stock->receiveGoodsReceipt($grn, $this->user->id);

        return $batches[0];
    }

    public function test_receiving_a_grn_creates_a_sellable_batch_and_an_in_movement(): void
    {
        $batch = $this->receive('B001', now()->addYear()->toDateString(), 100);

        $this->assertEquals(100, $batch->qty_remaining);
        $this->assertEquals('good', $batch->condition);
        $this->assertDatabaseHas('stock_movements', [
            'batch_id' => $batch->id, 'type' => 'in', 'qty_signed' => 100,
        ]);
    }

    public function test_fefo_allocates_from_earliest_expiry_batch_first(): void
    {
        $later = $this->receive('B-LATER', now()->addYear()->toDateString(), 50);
        $sooner = $this->receive('B-SOONER', now()->addMonths(2)->toDateString(), 50);

        $allocations = $this->stock->allocateFefo($this->product->id, 30, 'test', 1, $this->user->id);

        $this->assertCount(1, $allocations);
        $this->assertEquals($sooner->id, $allocations[0]['batch_id']);
        $this->assertEquals(30, $allocations[0]['qty']);

        $this->assertEquals(20, $sooner->refresh()->qty_remaining);
        $this->assertEquals(50, $later->refresh()->qty_remaining);
    }

    public function test_fefo_spans_multiple_batches_when_one_is_not_enough(): void
    {
        $sooner = $this->receive('B-SOONER', now()->addMonths(2)->toDateString(), 20);
        $later = $this->receive('B-LATER', now()->addYear()->toDateString(), 50);

        $allocations = $this->stock->allocateFefo($this->product->id, 35, 'test', 1, $this->user->id);

        $this->assertCount(2, $allocations);
        $this->assertEquals(0, $sooner->refresh()->qty_remaining);
        $this->assertEquals(35, $later->refresh()->qty_remaining);
    }

    public function test_fefo_throws_and_changes_nothing_when_stock_is_insufficient(): void
    {
        $batch = $this->receive('B001', now()->addYear()->toDateString(), 10);

        try {
            $this->stock->allocateFefo($this->product->id, 999, 'test', 1, $this->user->id);
            $this->fail('Expected RuntimeException for insufficient stock');
        } catch (RuntimeException $e) {
            // expected
        }

        $this->assertEquals(10, $batch->refresh()->qty_remaining);
        $this->assertDatabaseMissing('stock_movements', ['type' => 'out']);
    }

    public function test_fefo_ignores_expired_and_quarantined_batches(): void
    {
        $expired = $this->receive('B-EXPIRED', now()->subDay()->toDateString(), 50);
        $good = $this->receive('B-GOOD', now()->addYear()->toDateString(), 50);

        $quarantined = $this->receive('B-QUARANTINE', now()->addYear()->toDateString(), 50);
        $quarantined->update(['condition' => 'quarantine']);

        $allocations = $this->stock->allocateFefo($this->product->id, 50, 'test', 1, $this->user->id);

        $this->assertCount(1, $allocations);
        $this->assertEquals($good->id, $allocations[0]['batch_id']);
    }

    public function test_return_to_original_batch_preserves_expiry_when_condition_is_good(): void
    {
        $batch = $this->receive('B001', '2027-01-01', 50);
        $this->stock->allocateFefo($this->product->id, 20, 'test', 1, $this->user->id);

        $returned = $this->stock->returnToBatch($batch->refresh(), 5, 'good', 'client refused delivery', 1, $this->user->id);

        $this->assertEquals($batch->id, $returned->id);
        $this->assertEquals('2027-01-01', $returned->expiry_date->toDateString());
        $this->assertEquals(35, $returned->qty_remaining); // 50 - 20 + 5
    }

    public function test_return_with_damaged_condition_creates_a_quarantined_batch_not_sellable(): void
    {
        $batch = $this->receive('B001', now()->addYear()->toDateString(), 50);

        $returned = $this->stock->returnToBatch($batch, 5, 'damaged', 'crushed in transit', 1, $this->user->id);

        $this->assertNotEquals($batch->id, $returned->id);
        $this->assertEquals('damaged', $returned->condition);
        $this->assertFalse($returned->isSellable());
        $this->assertEquals(50, $batch->refresh()->qty_remaining); // original untouched
    }

    public function test_adjust_stock_logs_the_diff_with_a_reason(): void
    {
        $batch = $this->receive('B001', now()->addYear()->toDateString(), 50);

        $this->stock->adjustStock($batch, 47, 'physical count short by 3', $this->user->id);

        $this->assertEquals(47, $batch->refresh()->qty_remaining);
        $this->assertDatabaseHas('stock_movements', [
            'batch_id' => $batch->id, 'type' => 'adjust', 'qty_signed' => -3, 'reason' => 'physical count short by 3',
        ]);
    }
}
