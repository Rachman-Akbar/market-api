<?php

namespace App\Domains\Orders\Domain\Services;

use Illuminate\Support\Collection;

final class OrderPricingService
{
    /**
     * @param Collection<int, mixed> $items
     */
    public function calculateTotal(Collection $items): float
    {
        return (float) $items->sum(function ($item): float {
            return (float) $item->product->price * (int) $item->qty;
        });
    }
}
