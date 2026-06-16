<?php

declare(strict_types=1);

namespace App\Domains\Cart\Application\UseCases;

use App\Domains\Cart\Application\DTOs\AddCartItemData;
use App\Domains\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Cart\Domain\Repositories\CartRepositoryInterface;
use App\Domains\Cart\Domain\ValueObjects\Money;
use App\Domains\Cart\Domain\ValueObjects\Quantity;
use App\Domains\Cart\Infrastructure\Services\CartStockValidator;
use Illuminate\Support\Facades\DB;

final readonly class AddItemToCartUseCase
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private CartStockValidator $stockValidator,
    ) {
    }

    public function execute(AddCartItemData $data): CartSummaryData
    {
        return DB::transaction(function () use ($data): CartSummaryData {
            $cart = $this->carts->getOrCreateActiveByUserId($data->userId, lock: true);

            $targetQuantity = $cart->currentQuantityForProduct($data->productId) + $data->quantity;
            $product = $this->stockValidator->ensureProductAvailable($data->productId, $targetQuantity);

            $cart->addItem(
                productId: $data->productId,
                quantity: Quantity::fromInt($data->quantity),
                priceSnapshot: Money::fromInt((int) $product['price']),
                productNameSnapshot: (string) $product['name'],
                productImageSnapshot: $product['image'] ?? null,
            );

            return CartSummaryData::fromCart($this->carts->save($cart));
        }, 3);
    }
}
