<?php

namespace App\Domains\Inventory\Presentation\Http\Controllers;

use App\Domains\Inventory\Application\Actions\UpdateStockAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InventoryController extends Controller
{
    public function updateStock(Request $request, UpdateStockAction $action): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity_delta' => ['required', 'integer'],
        ]);

        $stock = $action->execute(
            (int) $validated['product_id'],
            (int) $validated['quantity_delta'],
            'seller_update',
        );

        return response()->json([
            'data' => [
                'product_id' => $stock->product_id,
                'quantity' => (int) $stock->quantity,
                'reserved_quantity' => (int) $stock->reserved_quantity,
                'available_quantity' => $stock->available_quantity,
            ],
        ]);
    }
}
