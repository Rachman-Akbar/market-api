<?php

namespace App\Domains\Payments\Presentation\Http\Controllers;

use App\Domains\Inventory\Application\Actions\DeductReservedStockAction;
use App\Domains\Inventory\Application\Actions\ReleaseReservedStockAction;
use App\Domains\Payments\Application\Actions\MarkPaymentStatusAction;
use App\Events\OrderUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    public function updateStatus(Request $request, MarkPaymentStatusAction $action, DeductReservedStockAction $deductReservedStock, ReleaseReservedStockAction $releaseReservedStock): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'status' => ['required', 'in:completed,failed'],
        ]);

        $order = Order::query()->with('items')->findOrFail((int) $validated['order_id']);
        $payment = $action->execute((int) $order->id, $validated['status']);

        foreach ($order->items as $item) {
            if ($validated['status'] === 'completed') {
                $deductReservedStock->execute((int) $item->product_id, (int) $item->qty, (string) $order->id);
            } else {
                $releaseReservedStock->execute((int) $item->product_id, (int) $item->qty, (string) $order->id);
            }
        }

        $order->status = $validated['status'] === 'completed' ? 'paid' : 'payment_failed';
        $order->save();

        event(new OrderUpdated((int) $order->id, $order->status));

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'payment_status' => $payment->status,
            ],
        ]);
    }
}
