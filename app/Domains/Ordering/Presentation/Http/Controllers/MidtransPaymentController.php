<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\MidtransSnapService;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
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
                $query->where('order_number', $order);

                if (ctype_digit($order)) {
                    $query->orWhere('id', (int) $order);
                }
            })
            ->with(['items', 'latestPaymentAttempt'])
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

        if (in_array($orderModel->payment_status, ['cancelled', 'refunded'], true)) {
            return response()->json([
                'message' => 'Order tidak bisa dibayar dengan status pembayaran saat ini.',
            ], 422);
        }

        if ($orderModel->status === 'cancelled') {
            return response()->json([
                'message' => 'Order sudah dibatalkan dan tidak bisa dibayar.',
            ], 422);
        }

        if ($orderModel->payment_gateway !== null && $orderModel->payment_gateway !== 'midtrans') {
            return response()->json([
                'message' => 'Order ini tidak menggunakan pembayaran Midtrans.',
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
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal membuat transaksi Midtrans.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }
}
