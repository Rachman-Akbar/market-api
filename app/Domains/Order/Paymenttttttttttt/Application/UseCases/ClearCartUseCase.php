<?php

declare(strict_types=1);

namespace App\Domains\Cart\Application\UseCases;

use App\Domains\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Cart\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class ClearCartUseCase
{
    public function __construct(private CartRepositoryInterface $carts)
    {
    }

    public function execute(string $userId): CartSummaryData
    {
        return DB::transaction(function () use ($userId): CartSummaryData {
            $cart = $this->carts->getOrCreateActiveByUserId($userId, lock: true);
            $cart->clear();

            return CartSummaryData::fromCart($this->carts->save($cart));
        }, 3);
    }
}
