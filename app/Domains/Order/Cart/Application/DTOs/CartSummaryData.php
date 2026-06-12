<?php

declare(strict_types=1);

namespace App\Domains\Cart\Application\DTOs;

use App\Domains\Cart\Domain\Entities\Cart;
use App\Domains\Cart\Domain\Entities\CartItem;

final readonly class CartSummaryData
{
    /** @param array<string, mixed> $payload */
    public function __construct(private array $payload)
    {
    }

    public static function fromCart(Cart $cart): self
    {
        return new self([
            'id' => $cart->id(),
            'user_id' => $cart->userId(),
            'active_user_id' => $cart->activeUserId(),
            'status' => $cart->status()->value,
            'total_quantity' => $cart->totalQuantity(),
            'subtotal' => $cart->subtotal()->value(),
            'items' => array_map(
                static fn (CartItem $item): array => [
                    'id' => $item->id(),
                    'cart_id' => $item->cartId(),
                    'product_id' => $item->productId(),
                    'quantity' => $item->quantity()->value(),
                    'price_snapshot' => $item->priceSnapshot()->value(),
                    'subtotal' => $item->subtotal()->value(),
                    'product_name_snapshot' => $item->productNameSnapshot(),
                    'product_image_snapshot' => $item->productImageSnapshot(),
                ],
                $cart->items(),
            ),
        ]);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->payload;
    }
}
