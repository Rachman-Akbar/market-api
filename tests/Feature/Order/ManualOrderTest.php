<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Catalog\Product\Application\UseCases\Product\CreateProductUseCase;
use App\Domains\Seller\Stock\Application\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

/**
 * Endpoint POST /api/v1/order/orderings/manual — order manual seller
 * (fungsi kasir) tanpa Midtrans.
 */
class ManualOrderTest extends TestCase
{
    use InteractsAsUser;
    use RefreshDatabase;

    private int $storeId;

    private int $variantId;

    private $sellerA;

    protected function setUp(): void
    {
        parent::setUp();

        [$sellerA, $store] = $this->actingAsSeller();
        $this->sellerA = $sellerA;
        $this->storeId = (int) $store->id;

        $this->makeRole('buyer');

        $product = $this->app->make(CreateProductUseCase::class)->execute([
            'store_id' => $this->storeId,
            'name' => 'Produk Kasir',
            'price' => 50000,
            'variants' => [
                ['name' => 'Standar', 'price' => 50000, 'po_stock' => 0],
            ],
        ]);

        $this->variantId = (int) $product->variants()[0]->id();

        $this->app->make(StockMovementService::class)->adjust([
            'variant_id' => $this->variantId,
            'quantity_delta' => 10,
            'movement_type' => 'inbound',
            'notes' => 'Stok awal untuk tes',
        ], $this->storeId);
    }

    private function basePayload(): array
    {
        return [
            'customer_name' => 'Pembeli Manual',
            'customer_phone' => '081230003000',
            'customer_email' => null,
            'address' => 'Jl. Kasir No. 9, Jakarta',
            'courier' => 'jne',
            'service' => 'Reguler',
            'shipping_cost' => 15000,
            'payment_method' => 'tunai_toko',
            'payment_status' => 'paid',
            'status' => 'processing',
            'items' => [
                ['variant_id' => $this->variantId, 'quantity' => 2],
            ],
        ];
    }

    public function test_happy_path_paid_commits_stock_and_creates_guest_buyer(): void
    {
        $response = $this->postJson('/api/v1/order/orderings/manual', $this->basePayload());

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $order = DB::table('orders')->where('order_number', $response->json('data.order_number'))->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertNotNull($order->received_at);
        $this->assertEqualsWithDelta(115000, (float) $order->total_amount, 0.01);
        $this->assertStringStartsWith('MAN-', (string) $order->order_number);

        $subOrderId = DB::table('sub_orders')->where('order_id', $order->id)->value('id');
        $item = DB::table('order_items')->where('sub_order_id', $subOrderId)->where('variant_id', $this->variantId)->first();
        $this->assertNotNull($item);
        $this->assertSame(2, (int) $item->quantity);

        $variantStock = DB::table('product_variants')->where('id', $this->variantId)->first();
        $this->assertSame(8, (int) $variantStock->stock);
        $this->assertSame(0, (int) $variantStock->stock_reserved);

        $buyer = DB::table('users')->where('id', $order->user_id)->first();
        $this->assertSame('Pembeli Manual', $buyer->name);
        $this->assertStringContainsString('@manual.order', (string) $buyer->email);
        $role = DB::table('user_roles')->where('user_id', $order->user_id)->value('role_id');

        $this->assertNotNull($role);
    }

    public function test_reuses_existing_buyer_when_email_matches(): void
    {
        $existing = $this->makeUser(['name' => 'Akun Lama', 'email' => 'buyer@example.com'], ['buyer']);

        $payload = $this->basePayload();
        $payload['customer_email'] = 'buyer@example.com';

        $response = $this->postJson('/api/v1/order/orderings/manual', $payload)->assertStatus(201);

        $order = DB::table('orders')->where('order_number', $response->json('data.order_number'))->first();
        $this->assertSame((string) $existing->id, (string) $order->user_id);
        $this->assertSame(1, DB::table('users')->where('email', 'buyer@example.com')->count());
    }

    public function test_unpaid_order_is_pending_and_does_not_commit_stock(): void
    {
        $payload = $this->basePayload();
        $payload['payment_status'] = 'unpaid';
        $payload['status'] = 'processing';

        $response = $this->postJson('/api/v1/order/orderings/manual', $payload)->assertStatus(201);

        $order = DB::table('orders')->where('order_number', $response->json('data.order_number'))->first();
        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertNull($order->received_at);

        $payment = DB::table('payments')->where('order_number', $order->order_number)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);

        $variantStock = DB::table('product_variants')->where('id', $this->variantId)->first();
        $this->assertSame(10, (int) $variantStock->stock);
        $this->assertSame(2, (int) $variantStock->stock_reserved);
    }

    public function test_variant_of_other_store_is_rejected_with_422(): void
    {
        $otherSeller = $this->actingAsSeller();
        $foreignProduct = $this->app->make(CreateProductUseCase::class)->execute([
            'store_id' => (int) $otherSeller[1]->id,
            'name' => 'Produk Toko Lain',
            'price' => 25000,
            'variants' => [
                ['name' => 'Standar', 'price' => 25000, 'po_stock' => 0],
            ],
        ]);
        $foreignVariantId = (int) $foreignProduct->variants()[0]->id();

        $payload = $this->basePayload();
        $payload['items'] = [['variant_id' => $foreignVariantId, 'quantity' => 1]];

        $this->postJson('/api/v1/order/orderings/manual', $payload)
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_insufficient_stock_is_rejected_with_422(): void
    {
        $payload = $this->basePayload();
        $payload['items'] = [['variant_id' => $this->variantId, 'quantity' => 99]];

        $this->postJson('/api/v1/order/orderings/manual', $payload)
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_non_seller_is_forbidden(): void
    {
        $this->actingAsRole('buyer');

        $this->postJson('/api/v1/order/orderings/manual', $this->basePayload())
            ->assertForbidden();
    }

    public function test_unpaid_pending_order_can_be_deleted_and_stock_released(): void
    {
        $payload = $this->basePayload();
        $payload['payment_status'] = 'unpaid';
        $payload['status'] = 'pending';

        $created = $this->postJson('/api/v1/order/orderings/manual', $payload)->assertStatus(201)->json('data.order_number');

        $order = DB::table('orders')->where('order_number', $created)->first();
        $subOrder = DB::table('sub_orders')->where('order_id', $order->id)->first();

        $response = $this->deleteJson('/api/v1/order/orderings/'.$subOrder->id);

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNull(DB::table('sub_orders')->find($subOrder->id));
        $this->assertNull(DB::table('orders')->find($order->id));
        $this->assertSame(0, DB::table('payments')->where('order_number', $created)->count());

        $variantStock = DB::table('product_variants')->where('id', $this->variantId)->first();
        $this->assertSame(10, (int) $variantStock->stock);
        $this->assertSame(0, (int) $variantStock->stock_reserved);
    }

    public function test_paid_order_cannot_be_deleted(): void
    {
        $created = $this->postJson('/api/v1/order/orderings/manual', $this->basePayload())->assertStatus(201)->json('data.order_number');

        $order = DB::table('orders')->where('order_number', $created)->first();
        $subOrder = DB::table('sub_orders')->where('order_id', $order->id)->first();

        $this->deleteJson('/api/v1/order/orderings/'.$subOrder->id)
            ->assertStatus(403);

        $this->assertNotNull(DB::table('sub_orders')->find($subOrder->id));
    }

    public function test_order_of_other_store_cannot_be_deleted(): void
    {
        $otherUser = $this->makeUser();
        $foreignStore = $this->makeStore($otherUser);

        $foreignOrderId = (int) DB::table('orders')->insertGetId([
            'order_number' => 'DOL-'.Str::upper(Str::random(6)),
            'user_id' => $otherUser->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'tunai_toko',
            'shipping_address' => 'Alamat pihak lain',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $foreignSubOrderId = (int) DB::table('sub_orders')->insertGetId([
            'order_id' => $foreignOrderId,
            'store_id' => $foreignStore->id,
            'sub_order_number' => 'DOL-SUB-'.Str::upper(Str::random(6)),
            'total_items_price' => 10000,
            'shipping_cost' => 0,
            'courier' => 'jne',
            'destination_id' => 'MANUAL-X',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson('/api/v1/order/orderings/'.$foreignSubOrderId)
            ->assertStatus(403);

        $this->assertNotNull(DB::table('sub_orders')->find($foreignSubOrderId));
    }
}
