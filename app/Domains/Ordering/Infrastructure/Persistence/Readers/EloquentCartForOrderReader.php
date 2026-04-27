<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Readers;

use App\Domains\Ordering\Domain\Repositories\CartForOrderReaderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class EloquentCartForOrderReader implements CartForOrderReaderInterface
{
    public function getActiveCartForUser(int $userId): ?array
    {
        $cartQuery = DB::table('carts')->where('user_id', $userId);

        if (Schema::hasColumn('carts', 'status')) {
            $cartQuery->whereIn('status', ['active', 'open']);
        }

        $cart = $cartQuery->latest('id')->first();

        if (! $cart) {
            return null;
        }

        $selects = [
            'cart_items.product_id',
            'cart_items.quantity',
            'products.name as product_name',
            'products.price as unit_price',
        ];

        $selects[] = Schema::hasColumn('products', 'sku')
            ? 'products.sku'
            : DB::raw('NULL as sku');

        $selects[] = Schema::hasColumn('products', 'currency')
            ? 'products.currency'
            : DB::raw("'IDR' as currency");

        $items = DB::table('cart_items')
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('cart_items.cart_id', $cart->id)
            ->select($selects)
            ->get()
            ->map(static fn ($item): array => [
                'product_id' => (int) $item->product_id,
                'product_name' => (string) $item->product_name,
                'sku' => $item->sku ? (string) $item->sku : null,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'currency' => (string) $item->currency,
            ])
            ->all();

        return [
            'id' => (int) $cart->id,
            'items' => $items,
        ];
    }

    public function markAsOrdered(int $cartId, int $orderId): void
    {
        $payload = [];

        if (Schema::hasColumn('carts', 'status')) {
            $payload['status'] = 'ordered';
        }

        if (Schema::hasColumn('carts', 'order_id')) {
            $payload['order_id'] = $orderId;
        }

        if (Schema::hasColumn('carts', 'checked_out_at')) {
            $payload['checked_out_at'] = now();
        }

        if ($payload !== []) {
            DB::table('carts')->where('id', $cartId)->update($payload);
        }
    }
}
