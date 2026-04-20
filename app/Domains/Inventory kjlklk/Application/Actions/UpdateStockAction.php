<?php

namespace App\Domains\Inventory\Application\Actions;

use App\Models\Stock;
use App\Models\StockMovement;

final class UpdateStockAction
{
    public function execute(int $productId, int $quantityDelta, string $referenceId = 'manual'): Stock
    {
        $stock = Stock::query()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 0, 'reserved_quantity' => 0],
        );

        $stock->quantity = max(0, ((int) $stock->quantity) + $quantityDelta);
        $stock->save();

        StockMovement::query()->create([
            'product_id' => $productId,
            'type' => $quantityDelta >= 0 ? 'IN' : 'OUT',
            'quantity' => abs($quantityDelta),
            'reference_type' => 'stock_update',
            'reference_id' => $referenceId,
        ]);

        return $stock->refresh();
    }
}
