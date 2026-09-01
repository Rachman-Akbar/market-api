<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Catalog\Product\Application\UseCases\Product\CreateProductUseCase;
use App\Domains\Order\Cart\Infrastructure\Persistence\Models\CartModel;
use App\Domains\Order\Ordering\Application\UseCases\CancelOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\CreateOrderUseCase;
use App\Domains\Seller\Stock\Application\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

class PoStockSplitTest extends TestCase
{
    use InteractsAsUser;
    use RefreshDatabase;

    private int $storeId;

    private int $variantId;

    protected function setUp(): void
    {
        parent::setUp();

        [$seller, $store] = $this->actingAsSeller();
        $this->storeId = (int) $store->id;

        $product = $this->app->make(CreateProductUseCase::class)->execute([
            'store_id' => $this->storeId,
            'name' => 'Produk Stok PO',
            'price' => 100000,
            'variants' => [
                ['name' => 'Standar', 'price' => 100000, 'po_stock' => 30],
            ],
        ]);

        $variantId = (int) $product->variants()[0]->id();
        $this->variantId = $variantId;

        $this->app->make(StockMovementService::class)->adjust([
            'variant_id' => $variantId,
            'quantity_delta' => 20,
            'movement_type' => 'inbound',
            'notes' => 'Stok awal untuk tes',
        ], $this->storeId);
    }

    private function buyerWithCartItem(int $quantity, int $userIdPrefix): string
    {
        $buyer = $this->makeUser(['name' => 'Buyer PO '.random_int(1, 99999)]);
        CartModel::query()->create(['user_id' => $buyer->id]);
        $cart = CartModel::query()->where('user_id', $buyer->id)->firstOrFail();
        $cart->items()->create([
            'product_variant_id' => $this->variantId,
            'quantity' => $quantity,
        ]);

        return (string) $buyer->id;
    }

    private function placeOrder(string $userId, int $quantity, string $orderType): int
    {
        $cart = CartModel::query()->where('user_id', $userId)->firstOrFail();
        $cartItemId = (int) $cart->items()->firstOrFail()->id;

        $order = $this->app->make(CreateOrderUseCase::class)->execute(
            userId: $userId,
            addressId: null,
            cartItemIds: [$cartItemId],
            courier: 'ambil_sendiri',
            service: null,
            paymentMethod: 'tunai_toko',
            voucherCode: null,
            orderType: $orderType,
            preorderReleaseAt: $orderType === 'preorder' ? now()->addDays(7)->toDateTimeString() : null,
            bookingExpiresAt: null
        );

        return (int) $order->id;
    }

    private function variantRow(): object
    {
        return DB::table('product_variants')->where('id', $this->variantId)->first();
    }

    public function test_normal_order_decrements_only_regular_stock(): void
    {
        $buyerId = $this->buyerWithCartItem(5, 1);
        $orderId = $this->placeOrder($buyerId, 5, 'normal');

        $row = $this->variantRow();
        $this->assertSame(15, (int) $row->stock);
        $this->assertSame(30, (int) $row->po_stock);
        $this->assertSame(45, (int) $row->stock + (int) $row->po_stock);
        $this->assertGreaterThan(0, $orderId);
    }

    public function test_preorder_order_decrements_only_po_stock(): void
    {
        $buyerId = $this->buyerWithCartItem(10, 2);
        $orderId = $this->placeOrder($buyerId, 10, 'preorder');

        $row = $this->variantRow();
        $this->assertSame(20, (int) $row->stock);
        $this->assertSame(20, (int) $row->po_stock);
        $this->assertSame(40, (int) $row->stock + (int) $row->po_stock);
        $this->assertGreaterThan(0, $orderId);
    }

    public function test_preorder_overflows_regular_stock_is_rejected(): void
    {
        $buyerId = $this->buyerWithCartItem(999, 3);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('stok pre-order (PO)');

        $this->placeOrder($buyerId, 999, 'preorder');
    }

    public function test_cancelling_preorder_restores_po_stock_but_keeps_regular_stock(): void
    {
        $buyerId = $this->buyerWithCartItem(10, 4);
        $orderId = $this->placeOrder($buyerId, 10, 'preorder');

        $this->app->make(CancelOrderUseCase::class)->execute($orderId);

        $row = $this->variantRow();
        $this->assertSame(20, (int) $row->stock);
        $this->assertSame(30, (int) $row->po_stock);
        $this->assertSame(50, (int) $row->stock + (int) $row->po_stock);
    }

    public function test_cancelling_normal_order_restores_regular_stock(): void
    {
        $buyerId = $this->buyerWithCartItem(5, 5);
        $orderId = $this->placeOrder($buyerId, 5, 'normal');

        $this->app->make(CancelOrderUseCase::class)->execute($orderId);

        $row = $this->variantRow();
        $this->assertSame(20, (int) $row->stock);
        $this->assertSame(30, (int) $row->po_stock);
        $this->assertSame(50, (int) $row->stock + (int) $row->po_stock);
    }
}
