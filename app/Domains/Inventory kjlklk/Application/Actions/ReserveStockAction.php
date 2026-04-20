<?php

namespace App\Domains\Inventory\Application\Actions;

use App\Domains\Inventory\Domain\Services\StockAvailabilityService;
use App\Events\StockReserved;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

final class ReserveStockAction
{
    public function __construct(private readonly StockAvailabilityService $availability) {}

    public function execute(int $productId, int $qty, string $referenceId): Stock
    {
        $stock = Stock::query()->lockForUpdate()->findOrFail($productId);

        if (! $this->availability->canReserve($stock, $qty)) {
            throw ValidationException::withMessages([
                'stock' => ['Insufficient available stock for reservation.'],
            ]);
        }

        $stock->reserved_quantity = (int) $stock->reserved_quantity + $qty;
        $stock->save();

        StockMovement::query()->create([
            'product_id' => $productId,
            'type' => 'RESERVED',
            'quantity' => $qty,
            'reference_type' => 'order',
            'reference_id' => $referenceId,
        ]);

        event(new StockReserved($productId, $qty, $referenceId));

        return $stock->refresh();
    }
}
