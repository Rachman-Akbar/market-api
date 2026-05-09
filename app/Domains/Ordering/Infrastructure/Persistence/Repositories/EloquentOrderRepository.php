<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Readers;

use App\Domains\Ordering\Domain\Repositories\CartForOrderReaderInterface;
use App\Models\Cart;

final class EloquentCartForOrderReader implements CartForOrderReaderInterface
{
    public function getActiveCartForUser(string $userId): ?array
    {
        $cart = Cart::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->with(['items.product'])
            ->first();

        if (! $cart) {
            return null;
        }

        $items = $cart->items
            ->map(static function ($item): array {
                $product = $item->product;

                $quantity = max(0, (int) ($item->quantity ?? 0));

                $unitPrice = (float) (
                    $item->price
                    ?? $item->unit_price
                    ?? $product?->price
                    ?? 0
                );

                return [
                    'id' => (int) $item->id,
                    'product_id' => (int) ($item->product_id ?? $product?->id ?? 0),
                    'product_name' => (string) (
                        $product?->name
                        ?? $item->product_name
                        ?? 'Product'
                    ),
                    'sku' => $product?->sku
                        ?? $item->sku
                        ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $quantity * $unitPrice,
                    'currency' => 'IDR',
                ];
            })
            ->filter(static fn (array $item): bool => $item['quantity'] > 0 && $item['product_id'] > 0)
            ->values()
            ->all();

        return [
            'id' => (int) $cart->id,
            'items' => $items,
        ];
    }

    public function markAsOrdered(int $cartId, int $orderId): void
    {
        Cart::query()
            ->whereKey($cartId)
            ->update([
                'status' => 'ordered',
                'updated_at' => now(),
            ]);
    }
}