<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Client;
use App\Models\GoodsReceipt;
use App\Models\GrnItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\LedgerService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orders;
    private StockService $stock;
    private Product $product;
    private Client $client;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stock = new StockService();
        $this->orders = new OrderService($this->stock, new LedgerService(), new InvoiceService());

        $this->user = User::factory()->create();
        $this->client = Client::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Apex Diagnostics',
            'credit_limit' => 100000,
            'outstanding_balance' => 0,
        ]);

        $this->product = Product::create([
            'name' => 'Test Acetone', 'slug' => 'test-acetone-' . uniqid(), 'cas_number' => '67-64-1',
            'hsn_code' => '2914', 'pack_size' => '500ml', 'unit' => 'bottle', 'is_active' => true,
        ]);

        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $grn = GoodsReceipt::create(['supplier_id' => $supplier->id, 'received_at' => now(), 'received_by' => $this->user->id]);
        GrnItem::create([
            'grn_id' => $grn->id, 'product_id' => $this->product->id,
            'batch_no' => 'B001', 'expiry_date' => now()->addYear(), 'qty' => 100, 'purchase_price' => 100,
        ]);
        $this->stock->receiveGoodsReceipt($grn, $this->user->id);
    }

    private function makeOrder(float $qty, float $unitPrice = 320): Order
    {
        $order = Order::create([
            'client_id' => $this->client->id, 'order_type' => 'portal', 'status' => 'draft',
            'subtotal' => $qty * $unitPrice, 'gst' => 0, 'total' => $qty * $unitPrice,
        ]);

        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $this->product->id,
            'qty' => $qty, 'unit_price' => $unitPrice,
        ]);

        return $order->load('items.product');
    }

    public function test_confirm_allocates_stock_generates_invoice_and_posts_ledger(): void
    {
        $order = $this->makeOrder(20, 320);

        $confirmed = $this->orders->confirm($order, $this->user->id);

        $this->assertEquals('confirmed', $confirmed->status);
        $this->assertEquals(80, Batch::first()->qty_remaining);

        $this->assertDatabaseHas('order_allocations', [
            'order_item_id' => $order->items->first()->id, 'qty' => 20,
        ]);

        $invoice = $confirmed->invoice;
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('GE/', $invoice->invoice_no);
        $this->assertNotNull($invoice->pdf_path);

        $this->assertEquals(6400, (float) $this->client->refresh()->outstanding_balance);
        $this->assertDatabaseHas('ledger_entries', [
            'client_id' => $this->client->id, 'type' => 'invoice', 'amount_signed' => 6400, 'balance_after' => 6400,
        ]);
    }

    public function test_confirm_throws_and_rolls_back_everything_when_credit_limit_exceeded(): void
    {
        $this->client->update(['credit_limit' => 1000]);
        $order = $this->makeOrder(20, 320); // total 6400 > limit 1000

        try {
            $this->orders->confirm($order, $this->user->id);
            $this->fail('Expected RuntimeException for credit limit exceeded');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Credit limit exceeded', $e->getMessage());
        }

        $this->assertEquals('draft', $order->refresh()->status);
        $this->assertEquals(100, Batch::first()->qty_remaining); // untouched
        $this->assertEquals(0, (float) $this->client->refresh()->outstanding_balance);
        $this->assertDatabaseCount('ledger_entries', 0);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_confirm_throws_and_rolls_back_everything_when_stock_is_insufficient(): void
    {
        $order = $this->makeOrder(500, 320); // only 100 in stock

        try {
            $this->orders->confirm($order, $this->user->id);
            $this->fail('Expected RuntimeException for insufficient stock');
        } catch (RuntimeException $e) {
            // expected
        }

        $this->assertEquals('draft', $order->refresh()->status);
        $this->assertEquals(100, Batch::first()->qty_remaining);
        $this->assertEquals(0, (float) $this->client->refresh()->outstanding_balance);
        $this->assertDatabaseCount('ledger_entries', 0);
        $this->assertDatabaseCount('order_allocations', 0);
    }

    public function test_second_order_balance_after_accumulates_on_top_of_first(): void
    {
        $first = $this->makeOrder(10, 320); // 3200
        $this->orders->confirm($first, $this->user->id);

        $second = $this->makeOrder(5, 320); // 1600
        $this->orders->confirm($second, $this->user->id);

        $this->assertEquals(4800, (float) $this->client->refresh()->outstanding_balance);
        $this->assertDatabaseHas('ledger_entries', ['balance_after' => 3200]);
        $this->assertDatabaseHas('ledger_entries', ['balance_after' => 4800]);
    }
}
