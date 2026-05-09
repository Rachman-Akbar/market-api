<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Readers;

use App\Domains\Ordering\Domain\Repositories\CartForOrderReaderInterface;
use Illuminate\Support\Facades\DB;

final class EloquentCartForOrderReader implements CartForOrderReaderInterface
{
    public function getActiveCartForUser(string $userId): ?array
    {
        $cart = DB::table('carts')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (! $cart) {
            return null;
        }

        $items = DB::table('cart_items')
            ->where('cart_id', $cart->id)
            ->get()
            ->map(static function (object $item): array {
                $quantity = max(0, (int) ($item->quantity ?? 0));

                $unitPrice = (float) (
                    $item->price_snapshot
                    ?? $item->price
                    ?? $item->unit_price
                    ?? 0
                );

                return [
                    'id' => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'product_name' => (string) (
                        $item->product_name_snapshot
                        ?? $item->product_name
                        ?? 'Product'
                    ),
                    'sku' => $item->sku ?? null,
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
        DB::table('carts')
            ->where('id', $cartId)
            ->update([
                'status' => 'ordered',
                'updated_at' => now(),
            ]);
    }
}