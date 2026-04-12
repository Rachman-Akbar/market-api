<?php

namespace App\Domains\Orders\Application\Actions;

use App\Models\Cart;

final class AddToCartAction
{
    public function execute(string $userId, int $productId, int $qty): Cart
    {
        $cart = Cart::query()->firstOrCreate([
            'user_id' => $userId,
        ]);

        $item = $cart->items()->firstOrNew([
            'product_id' => $productId,
        ]);

        $item->qty = (int) ($item->qty ?? 0) + $qty;
        $item->save();

        return $cart->load('items.product');
    }
}
