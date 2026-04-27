<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Persistence\Mappers;

use App\Domains\Cart\Domain\Entities\Cart;
use App\Domains\Cart\Domain\ValueObjects\CartStatus;
use App\Domains\Cart\Infrastructure\Persistence\Models\CartModel;

final class CartMapper
{
    public function __construct(private readonly ?CartItemMapper $itemMapper = null)
    {
    }

    public function toDomain(CartModel $model): Cart
    {
        $mapper = $this->itemMapper ?? new CartItemMapper();

        if (! $model->relationLoaded('items')) {
            $model->load('items');
        }

        $items = $model->items
            ->map(static fn ($item) => $mapper->toDomain($item))
            ->all();

        return new Cart(
            id: (int) $model->id,
            userId: (string) $model->user_id,
            activeUserId: $model->active_user_id,
            status: CartStatus::fromDatabase((string) $model->status),
            items: $items,
        );
    }
}
