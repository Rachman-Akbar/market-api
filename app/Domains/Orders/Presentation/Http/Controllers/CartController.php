<?php

namespace App\Domains\Orders\Presentation\Http\Controllers;

use App\Domains\Orders\Application\Actions\AddToCartAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartController extends Controller
{
    public function add(Request $request, AddToCartAction $action): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $cart = $action->execute(
            $user->id,
            (int) $validated['product_id'],
            (int) $validated['qty'],
        );

        return response()->json([
            'data' => $cart,
        ]);
    }
}
