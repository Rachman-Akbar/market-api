<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\MidtransSnapService;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

final class MidtransPaymentController extends Controller
{
    public function create(
        Request $request,
        string $order,
        MidtransSnapService $midtrans,
    ): JsonResponse {
        $orderModel = Order::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($order): void {
                $query->where('order_number', $order)
                    ->orWhere('id', $order);
            })
            ->with('items')
            ->first();

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if ($orderModel->payment_status === 'paid') {
            return response()->json([
                'message' => 'Order sudah dibayar.',
            ], 422);
        }

        try {
            $payment = $midtrans->createTransaction($orderModel, $request->user());

            return response()->json([
                'message' => 'Midtrans payment berhasil dibuat.',
                'data' => [
                    'order_number' => $orderModel->order_number,
                    'payment' => $payment,
                ],
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Gagal membuat transaksi Midtrans.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 422);
        }
    }
}
