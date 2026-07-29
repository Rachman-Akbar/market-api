<?php

declare(strict_types=1);

namespace App\Domains\Template\Cart\Application\UseCases;

use App\Domains\Template\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Template\Cart\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class RemoveItemFromCartUseCase
{
    public function __construct(private CartRepositoryInterface $carts)
    {
    }

    public function execute(string $userId, int $productId): CartSummaryData
    {
        return DB::transaction(function () use ($userId, $productId): CartSummaryData {
            $cart = $this->carts->getOrCreateActiveByUserId($userId, lock: true);
            $cart->removeItem($productId);

            return CartSummaryData::fromCart($this->carts->save($cart));
        }, 3);
    }
}
