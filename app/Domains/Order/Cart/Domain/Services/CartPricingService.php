<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\Services;

use App\Domains\Cart\Domain\Entities\Cart;
use App\Domains\Cart\Domain\ValueObjects\Money;

final class CartPricingService
{
    public function subtotal(Cart $cart): Money
    {
        return $cart->subtotal();
    }
}
