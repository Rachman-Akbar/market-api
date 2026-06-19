<?php

declare(strict_types=1);

namespace App\Domains\Cart\Application\UseCases;

use App\Domains\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Cart\Application\DTOs\UpdateCartItemData;
use App\Domains\Cart\Domain\Repositories\CartRepositoryInterface;
use App\Domains\Cart\Domain\ValueObjects\Quantity;
use App\Domains\Cart\Infrastructure\Services\CartStockValidator;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCartItemQuantityUseCase
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private CartStockValidator $stockValidator,
    ) {
    }

    public function execute(UpdateCartItemData $data): CartSummaryData
    {
        return DB::transaction(function () use ($data): CartSummaryData {
            $cart = $this->carts->getOrCreateActiveByUserId($data->userId, lock: true);

            $this->stockValidator->ensureProductAvailable($data->productId, $data->quantity);
            $cart->updateItemQuantity($data->productId, Quantity::fromInt($data->quantity));

            return CartSummaryData::fromCart($this->carts->save($cart));
        }, 3);
    }
}
