<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Services;

use App\Domains\Ordering\Domain\Entities\OrderItem;
use App\Domains\Ordering\Domain\ValueObjects\Money;
use DomainException;

final class OrderTotalCalculator
{
    /** @param array<int, OrderItem> $items */
    public function calculateSubtotal(array $items): Money
    {
        if ($items === []) {
            throw new DomainException('Cannot calculate total for empty order items.');
        }

        $currency = $items[0]->subtotal()->currency();
        $total = Money::zero($currency);

        foreach ($items as $item) {
            $total = $total->add($item->subtotal());
        }

        return $total;
    }
}
