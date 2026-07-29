<?php

declare(strict_types=1);

namespace App\Domains\Template\Cart\Application\UseCases;

use App\Domains\Template\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Template\Cart\Domain\Repositories\CartRepositoryInterface;

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
