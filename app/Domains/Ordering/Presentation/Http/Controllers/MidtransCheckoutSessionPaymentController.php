<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\MidtransCheckoutSessionSnapService;
use App\Models\CheckoutSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
use Throwable;

final class MidtransCheckoutSessionPaymentController extends Controller
{
    public function create(
        Request $request,
        string $session,
        MidtransCheckoutSessionSnapService $midtrans,
    ): JsonResponse {
        $checkoutSession = CheckoutSession::query()
            ->where('user_id', (string) $request->user()->getAuthIdentifier())
            ->where(function ($query) use ($session): void {
                $query->where('session_number', $session);

                if (ctype_digit($session)) {
                    $query->orWhere('id', (int) $session);
                }
            })
            ->with(['items', 'latestPaymentAttempt'])
            ->first();

        if (! $checkoutSession) {
            return response()->json([
                'message' => 'Checkout session tidak ditemukan.',
            ], 404);
        }

        if ($checkoutSession->payment_method !== 'midtrans') {
            return response()->json([
                'message' => 'Checkout session ini tidak menggunakan Midtrans.',
            ], 422);
        }

        if ($checkoutSession->created_order_id) {
            return response()->json([
                'message' => 'Checkout session ini sudah menjadi order.',
            ], 422);
        }

        if (in_array($checkoutSession->status, ['paid', 'cancelled', 'expired', 'failed'], true)) {
            return response()->json([
                'message' => 'Checkout session tidak bisa dibayar dengan status saat ini.',
            ], 422);
        }

        try {
            $payment = $midtrans->createTransaction($checkoutSession, $request->user());

            return response()->json([
                'message' => 'Midtrans payment berhasil dibuat.',
                'data' => [
                    'session_number' => $checkoutSession->session_number,
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
