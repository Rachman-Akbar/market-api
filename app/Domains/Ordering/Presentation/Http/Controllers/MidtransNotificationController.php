<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

final class MidtransNotificationController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if (! $this->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Invalid Midtrans notification signature', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'message' => 'Invalid signature.',
            ], 403);
        }

        $order = Order::query()
            ->where('midtrans_order_id', $orderId)
            ->orWhere('order_number', $orderId)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $paymentType = $payload['payment_type'] ?? null;

        $paymentStatus = $this->mapPaymentStatus($transactionStatus, $fraudStatus);

        $orderStatus = $order->status;

        if ($paymentStatus === 'paid') {
            $orderStatus = 'confirmed';
        }

        if (in_array($paymentStatus, ['failed', 'cancelled'], true)) {
            $orderStatus = 'cancelled';
        }

        $order->forceFill([
            'payment_status' => $paymentStatus,
            'status' => $orderStatus,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $order->midtrans_transaction_id,
            'midtrans_payment_type' => is_string($paymentType) ? $paymentType : $order->midtrans_payment_type,
            'midtrans_payload' => $payload,
            'paid_at' => $paymentStatus === 'paid' ? now() : $order->paid_at,
        ])->save();

        return response()->json([
            'message' => 'OK',
        ]);
    }

    private function isValidSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey,
    ): bool {
        $serverKey = (string) config('midtrans.server_key');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signatureKey);
    }

    private function mapPaymentStatus(string $transactionStatus, string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'challenge' ? 'pending' : 'paid';
        }

        return match ($transactionStatus) {
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'failure' => 'failed',
            'cancel', 'expire' => 'cancelled',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };
    }
}
