<?php

namespace App\Domains\Inventory\Domain\Services;

use App\Models\Stock;

final class StockAvailabilityService
{
    public function available(Stock $stock): int
    {
        return max(0, (int) $stock->quantity - (int) $stock->reserved_quantity);
    }

    public function canReserve(Stock $stock, int $qty): bool
    {
        return $this->available($stock) >= $qty;
    }
}
