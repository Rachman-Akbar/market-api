<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

final class OrderPaymentStatusController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        try {
            $sessionNumber = trim((string) $request->query('session', ''));
            $gatewayOrderId = trim((string) $request->query('order_id', ''));

            if ($sessionNumber === '' && $gatewayOrderId === '') {
                return response()->json([
                    'message' => 'Parameter session atau order_id wajib diisi.',
                ], 422);
            }

            $checkoutSession = CheckoutSession::query()
                ->where('user_id', (string) $request->user()->getAuthIdentifier())
                ->when($sessionNumber !== '', function ($query) use ($sessionNumber): void {
                    $query->where('session_number', $sessionNumber);
                })
                ->when($sessionNumber === '' && $gatewayOrderId !== '', function ($query) use ($gatewayOrderId): void {
                    $query->where('midtrans_order_id', $gatewayOrderId);
                })
                ->with(['items'])
                ->first();

            if (! $checkoutSession) {
                return response()->json([
                    'message' => 'Checkout session tidak ditemukan.',
                ], 404);
            }

            $paymentAttempt = null;

            if ($gatewayOrderId !== '') {
                $paymentAttempt = PaymentAttempt::query()
                    ->where('checkout_session_id', $checkoutSession->id)
                    ->where('gateway_order_id', $gatewayOrderId)
                    ->latest('id')
                    ->first();
            }

            if (! $paymentAttempt) {
                $paymentAttempt = PaymentAttempt::query()
                    ->where('checkout_session_id', $checkoutSession->id)
                    ->latest('id')
                    ->first();
            }

            return response()->json([
                'message' => 'Status pembayaran berhasil diambil.',
                'data' => [
                    'session' => [
                        'id' => $checkoutSession->id,
                        'session_number' => $checkoutSession->session_number,
                        'status' => $checkoutSession->status,
                        'payment_method' => $checkoutSession->payment_method,
                        'payment_gateway' => $checkoutSession->payment_gateway,
                        'grand_total' => $checkoutSession->grand_total,
                        'created_order_id' => $checkoutSession->created_order_id,
                        'midtrans_order_id' => $checkoutSession->midtrans_order_id,
                        'midtrans_transaction_id' => $checkoutSession->midtrans_transaction_id,
                        'midtrans_transaction_status' => $checkoutSession->midtrans_transaction_status,
                        'midtrans_payment_type' => $checkoutSession->midtrans_payment_type,
                        'midtrans_fraud_status' => $checkoutSession->midtrans_fraud_status,
                        'paid_at' => $checkoutSession->paid_at,
                        'expires_at' => $checkoutSession->expires_at,
                    ],
                    'payment_attempt' => $paymentAttempt ? [
                        'id' => $paymentAttempt->id,
                        'gateway' => $paymentAttempt->gateway,
                        'gateway_order_id' => $paymentAttempt->gateway_order_id,
                        'status' => $paymentAttempt->status,
                        'gross_amount' => $paymentAttempt->gross_amount,
                        'snap_token' => $paymentAttempt->snap_token,
                        'redirect_url' => $paymentAttempt->redirect_url,
                        'expires_at' => $paymentAttempt->expires_at,
                    ] : null,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal mengambil status pembayaran.',
                'error' => config('app.debug') ? $exception->getMessage() : 'Internal Server Error',
                'file' => config('app.debug') ? $exception->getFile() : null,
                'line' => config('app.debug') ? $exception->getLine() : null,
            ], 500);
        }
    }
}
