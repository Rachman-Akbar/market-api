<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Persistence\Mappers;

use App\Domains\Cart\Domain\Entities\CartItem;
use App\Domains\Cart\Domain\ValueObjects\Money;
use App\Domains\Cart\Domain\ValueObjects\Quantity;
use App\Domains\Cart\Infrastructure\Persistence\Models\CartItemModel;

final class CartItemMapper
{
    public function toDomain(CartItemModel $model): CartItem
    {
        return new CartItem(
            id: (int) $model->id,
            cartId: (int) $model->cart_id,
            productId: (int) $model->product_id,
            quantity: Quantity::fromInt((int) $model->quantity),
            priceSnapshot: Money::fromInt((int) $model->price_snapshot),
            productNameSnapshot: (string) $model->product_name_snapshot,
            productImageSnapshot: $model->product_image_snapshot,
        );
    }
}
