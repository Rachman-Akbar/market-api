<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class MidtransWebhookController
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $gatewayOrderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if ($gatewayOrderId === '' || $signatureKey === '') {
            return response()->json([
                'message' => 'Invalid notification payload.',
            ], 400);
        }

        $expectedSignature = hash(
            'sha512',
            $gatewayOrderId . $statusCode . $grossAmount . config('midtrans.server_key')
        );

        if (! hash_equals($expectedSignature, $signatureKey)) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], 403);
        }

        DB::transaction(function () use ($payload, $gatewayOrderId): void {
            /** @var PaymentAttempt|null $attempt */
            $attempt = PaymentAttempt::query()
                ->where('gateway', 'midtrans')
                ->where('gateway_order_id', $gatewayOrderId)
                ->lockForUpdate()
                ->first();

            if (! $attempt) {
                throw new RuntimeException('Payment attempt tidak ditemukan.');
            }

            /** @var Order $order */
            $order = Order::query()
                ->whereKey($attempt->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $transactionStatus = (string) ($payload['transaction_status'] ?? '');
            $fraudStatus = (string) ($payload['fraud_status'] ?? '');
            $paymentType = (string) ($payload['payment_type'] ?? '');
            $transactionId = (string) ($payload['transaction_id'] ?? '');
            $statusCode = (string) ($payload['status_code'] ?? '');
            $statusMessage = (string) ($payload['status_message'] ?? '');

            $mappedStatus = $this->mapPaymentAttemptStatus($transactionStatus, $fraudStatus);
            $mappedOrderPaymentStatus = $this->mapOrderPaymentStatus($transactionStatus, $fraudStatus);

            $paidAt = $mappedOrderPaymentStatus === 'paid'
                ? now()
                : $attempt->paid_at;

            $expiredAt = $mappedOrderPaymentStatus === 'expired'
                ? now()
                : $attempt->expired_at;

            $attempt->forceFill([
                'gateway_transaction_id' => $transactionId ?: $attempt->gateway_transaction_id,
                'status' => $mappedStatus,
                'payment_type' => $paymentType ?: $attempt->payment_type,
                'transaction_status' => $transactionStatus ?: $attempt->transaction_status,
                'fraud_status' => $fraudStatus ?: $attempt->fraud_status,
                'provider_response_code' => $statusCode ?: $attempt->provider_response_code,
                'provider_response_message' => $statusMessage ?: $attempt->provider_response_message,
                'latest_notification_payload' => $payload,
                'paid_at' => $paidAt,
                'expired_at' => $expiredAt,
            ])->save();

            $order->forceFill([
                'payment_status' => $mappedOrderPaymentStatus,
                'payment_gateway' => 'midtrans',
                'payment_method' => $paymentType ?: $order->payment_method,

                'midtrans_order_id' => $gatewayOrderId,
                'midtrans_transaction_id' => $transactionId ?: $order->midtrans_transaction_id,
                'midtrans_payment_type' => $paymentType ?: $order->midtrans_payment_type,
                'midtrans_transaction_status' => $transactionStatus ?: $order->midtrans_transaction_status,
                'midtrans_fraud_status' => $fraudStatus ?: $order->midtrans_fraud_status,
                'midtrans_payload' => $payload,

                'paid_at' => $paidAt,
                'payment_failed_reason' => in_array($mappedOrderPaymentStatus, ['failed', 'cancelled', 'expired'], true)
                    ? ($statusMessage ?: $transactionStatus)
                    : null,
            ])->save();

            /*
             * Ini inti perbaikan cart:
             * cart hanya dikosongkan ketika payment sudah benar-benar paid.
             */
            if ($mappedOrderPaymentStatus === 'paid') {
                $this->clearCartItemsUsedByOrder($order);
            }
        });

        return response()->json([
            'message' => 'Notification processed.',
        ]);
    }

    private function mapPaymentAttemptStatus(string $transactionStatus, string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };
    }

    private function mapOrderPaymentStatus(string $transactionStatus, string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };
    }

    private function clearCartItemsUsedByOrder(Order $order): void
    {
        $cartItemIds = $order->source_cart_item_ids;

        if (! is_array($cartItemIds) || $cartItemIds === []) {
            return;
        }

        CartItem::query()
            ->where('user_id', $order->user_id)
            ->whereIn('id', $cartItemIds)
            ->delete();
    }
}
