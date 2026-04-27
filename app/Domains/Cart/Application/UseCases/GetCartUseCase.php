<?php

declare(strict_types=1);

namespace App\Domains\Cart\Application\UseCases;

use App\Domains\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Cart\Domain\Repositories\CartRepositoryInterface;

final readonly class GetCartUseCase
{
    public function __construct(private CartRepositoryInterface $carts)
    {
    }

    public function execute(string $userId): CartSummaryData
    {
        $cart = $this->carts->getOrCreateActiveByUserId($userId);

        return CartSummaryData::fromCart($cart);
    }
}
