<?php

namespace App\Domains\Orders\Application\Actions;

use App\Domains\Inventory\Application\Actions\ReserveStockAction;
use App\Domains\Orders\Domain\Services\OrderPricingService;
use App\Domains\Payments\Application\Actions\CreatePaymentIntentAction;
use App\Events\OrderPlaced;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CheckoutAction
{
    public function __construct(
        private readonly ReserveStockAction $reserveStock,
        private readonly CreatePaymentIntentAction $createPaymentIntent,
        private readonly OrderPricingService $pricing,
    ) {}

    /**
     * @return array{order: Order, payment_status: string}
     */
    public function execute(string $buyerId): array
    {
        return DB::transaction(function () use ($buyerId): array {
            $cart = Cart::query()->with(['items.product.stock'])->where('user_id', $buyerId)->first();
            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Cart is empty.'],
                ]);
            }

            $firstSellerId = (string) $cart->items->first()->product->seller_id;
            foreach ($cart->items as $item) {
                if ((string) $item->product->seller_id !== $firstSellerId) {
                    throw ValidationException::withMessages([
                        'cart' => ['Checkout currently supports single seller per order.'],
                    ]);
                }
            }

            $total = $this->pricing->calculateTotal($cart->items);

            $order = Order::query()->create([
                'buyer_id' => $buyerId,
                'seller_id' => $firstSellerId,
                'status' => 'pending_payment',
                'total_price' => $total,
            ]);

            foreach ($cart->items as $item) {
                $this->reserveStock->execute((int) $item->product_id, (int) $item->qty, (string) $order->id);

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'price' => $item->product->price,
                    'qty' => $item->qty,
                ]);
            }

            $payment = $this->createPaymentIntent->execute((int) $order->id, 'manual_placeholder');

            $cart->items()->delete();

            event(new OrderPlaced((int) $order->id, $buyerId, $firstSellerId));

            return [
                'order' => $order->load(['items.product', 'payment']),
                'payment_status' => $payment->status,
            ];
        });
    }
}
