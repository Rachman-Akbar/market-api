<?php

declare(strict_types=1);

namespace App\Domains\Template\Cart\Domain\Services;

use App\Domains\Template\Cart\Domain\Entities\Cart;
use App\Domains\Template\Cart\Domain\ValueObjects\Money;

final class CartPricingService
{
    public function subtotal(Cart $cart): Money
    {
        return $cart->subtotal();
    }
}
