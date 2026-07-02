<?php

namespace Tests\Feature\Api;

use App\Models\GoodsReceipt;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end through the HTTP layer: register -> login -> browse catalog ->
 * place order -> confirm. Exercises the actual routes the Vite/React
 * frontend will call, not just the service classes directly.
 */
class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum's stateful-SPA middleware only starts a session for requests
        // it recognises as coming from the frontend (Referer/Origin host must
        // match SANCTUM_STATEFUL_DOMAINS) — mirror what the Vite app's browser
        // requests carry automatically.
        $this->withHeader('Referer', 'http://localhost:3010');

        $this->product = Product::create([
            'name' => 'Acetone AR/ACS', 'slug' => 'acetone-ar-acs', 'cas_number' => '67-64-1',
            'pack_size' => '500ml', 'unit' => 'bottle', 'sales_price' => 320, 'is_active' => true,
        ]);

        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $staff = User::factory()->create();
        $grn = GoodsReceipt::create(['supplier_id' => $supplier->id, 'received_at' => now(), 'received_by' => $staff->id]);
        GrnItem::create([
            'grn_id' => $grn->id, 'product_id' => $this->product->id,
            'batch_no' => 'B001', 'expiry_date' => now()->addYear(), 'qty' => 100, 'purchase_price' => 100,
        ]);
        app(StockService::class)->receiveGoodsReceipt($grn, $staff->id);
    }

    public function test_product_catalog_is_publicly_browsable(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Acetone AR/ACS']);
    }

    public function test_full_register_login_order_confirm_flow(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Ramesh Iyer',
            'email' => 'ramesh@apexdiagnostics.test',
            'password' => 'password123',
            'company_name' => 'Apex Diagnostics',
            'gstin' => '27AAACA1234F1Z5',
        ]);

        $register->assertCreated();
        $register->assertJsonPath('client.company_name', 'Apex Diagnostics');

        // credit_limit defaults to 0 — raise it so the order below clears the gate.
        $clientId = $register->json('client.id');
        \App\Models\Client::find($clientId)->update(['credit_limit' => 50000]);

        $order = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 10],
            ],
        ]);

        $order->assertCreated();
        $order->assertJsonPath('status', 'draft');
        $order->assertJsonPath('subtotal', '3200.00');
        $orderId = $order->json('id');

        $confirm = $this->postJson("/api/orders/{$orderId}/confirm");

        $confirm->assertOk();
        $confirm->assertJsonPath('status', 'confirmed');
        $this->assertNotNull($confirm->json('invoice.invoice_no'));

        $this->assertEquals(90, $this->product->batches()->first()->qty_remaining);

        $history = $this->getJson('/api/orders');
        $history->assertOk();
        $history->assertJsonCount(1, 'data');
    }

    public function test_guest_cannot_place_an_order(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
        ]);

        $response->assertUnauthorized();
    }

    public function test_a_client_cannot_confirm_another_clients_order(): void
    {
        $userOne = User::factory()->create(['role' => 'client']);
        $clientOne = \App\Models\Client::create(['user_id' => $userOne->id, 'company_name' => 'One Co', 'credit_limit' => 50000]);
        $orderOne = $this->actingAs($userOne)
            ->postJson('/api/orders', ['items' => [['product_id' => $this->product->id, 'qty' => 1]]])
            ->json('id');

        $userTwo = User::factory()->create(['role' => 'client']);
        \App\Models\Client::create(['user_id' => $userTwo->id, 'company_name' => 'Two Co', 'credit_limit' => 50000]);

        $response = $this->actingAs($userTwo)->postJson("/api/orders/{$orderOne}/confirm");

        $response->assertForbidden();
    }
}
