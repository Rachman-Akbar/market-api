<?php

namespace App\Domains\Orders\Presentation\Http\Controllers;

use App\Domains\Orders\Application\Actions\CheckoutAction;
use App\Domains\Orders\Application\Actions\ListBuyerOrdersAction;
use App\Domains\Orders\Application\Actions\ListSellerOrdersAction;
use App\Events\OrderUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class OrderController extends Controller
{
    public function checkout(Request $request, CheckoutAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $result = $action->execute($user->id);

        return response()->json([
            'data' => $result,
        ], 201);
    }

    public function myOrders(Request $request, ListBuyerOrdersAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => $action->execute($user->id),
        ]);
    }

    public function sellerOrders(Request $request, ListSellerOrdersAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => $action->execute($user->id),
        ]);
    }

    public function sellerDecision(int $orderId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:accepted,rejected'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $order = Order::query()->findOrFail($orderId);
        if ((string) $order->seller_id !== (string) $user->id) {
            throw ValidationException::withMessages([
                'order' => ['You can only manage your own seller orders.'],
            ]);
        }

        $order->status = $validated['decision'];
        $order->save();

        event(new OrderUpdated((int) $order->id, $order->status));

        return response()->json([
            'data' => $order,
        ]);
    }
}
