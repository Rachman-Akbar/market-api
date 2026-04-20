<?php

namespace App\Domains\Inventory\Application\Actions;

use App\Models\Stock;
use App\Models\StockMovement;

final class ReleaseReservedStockAction
{
    public function execute(int $productId, int $qty, string $referenceId): Stock
    {
        $stock = Stock::query()->lockForUpdate()->findOrFail($productId);

        $stock->reserved_quantity = max(0, (int) $stock->reserved_quantity - $qty);
        $stock->save();

        StockMovement::query()->create([
            'product_id' => $productId,
            'type' => 'IN',
            'quantity' => $qty,
            'reference_type' => 'payment_failed_release',
            'reference_id' => $referenceId,
        ]);

        return $stock->refresh();
    }
}
