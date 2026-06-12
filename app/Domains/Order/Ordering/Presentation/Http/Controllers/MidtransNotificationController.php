<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\FinalizeCheckoutSessionService;
use App\Http\Controllers\Controller;
use App\Models\CheckoutSession;
use App\Models\MidtransNotification;
use App\Models\Order;
use App\Models\PaymentAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MidtransNotificationController extends Controller
{
    public function __invoke(
        Request $request,
        FinalizeCheckoutSessionService $finalizer,
    ): JsonResponse {
        $payload = $request->all();

        if (! $this->isValidSignature($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        DB::transaction(function () use ($payload, $finalizer): void {
            $gatewayOrderId = (string) ($payload['order_id'] ?? '');

            /** @var PaymentAttempt|null $attempt */
            $attempt = PaymentAttempt::query()
                ->where('gateway_order_id', $gatewayOrderId)
                ->lockForUpdate()
                ->first();

            if (! $attempt) {
                return;
            }

            /** @var CheckoutSession|null $session */
            $session = $attempt->checkout_session_id
                ? CheckoutSession::query()
                    ->whereKey($attempt->checkout_session_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            /** @var Order|null $order */
            $order = $attempt->order_id
                ? Order::query()
                    ->whereKey($attempt->order_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            $payloadHash = hash('sha256', json_encode($payload));

            MidtransNotification::query()->firstOrCreate(
                ['payload_hash' => $payloadHash],
                [
                    'payment_attempt_id' => $attempt->id,
                    'checkout_session_id' => $session?->id,
                    'order_id' => $order?->id,
                    'gateway_order_id' => $gatewayOrderId,
                    'gateway_transaction_id' => $payload['transaction_id'] ?? null,
                    'transaction_status' => $payload['transaction_status'] ?? null,
                    'payment_type' => $payload['payment_type'] ?? null,
                    'fraud_status' => $payload['fraud_status'] ?? null,
                    'signature_key' => $payload['signature_key'] ?? null,
                    'payload' => $payload,
                    'processed_at' => now(),
                    'received_at' => now(),
                ]
            );

            $transactionStatus = (string) ($payload['transaction_status'] ?? '');
            $fraudStatus = $payload['fraud_status'] ?? null;

            [$paymentStatus, $orderStatus] = $this->mapStatuses(
                $transactionStatus,
                $fraudStatus,
                $order?->status ?? 'pending',
            );

            $paymentInstructions = $this->extractPaymentInstructions($payload);

            $attempt->forceFill([
                'status' => $this->mapAttemptStatus($paymentStatus),
                'gateway_transaction_id' => $payload['transaction_id'] ?? $attempt->gateway_transaction_id,
                'payment_type' => $payload['payment_type'] ?? $attempt->payment_type,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'latest_notification_payload' => $payload,
                'payment_instructions' => $paymentInstructions,
                'paid_at' => $paymentStatus === 'paid' ? now() : $attempt->paid_at,
            ])->save();

            if ($session) {
                $session->forceFill([
                    'status' => $this->mapSessionStatus($paymentStatus),
                    'payment_gateway' => 'midtrans',
                    'payment_method' => $payload['payment_type'] ?? $session->payment_method,
                    'midtrans_order_id' => $gatewayOrderId,
                    'midtrans_transaction_id' => $payload['transaction_id'] ?? $session->midtrans_transaction_id,
                    'midtrans_payment_type' => $payload['payment_type'] ?? $session->midtrans_payment_type,
                    'midtrans_transaction_status' => $transactionStatus,
                    'midtrans_fraud_status' => $fraudStatus,
                    'midtrans_payload' => $payload,
                    'payment_instructions' => $paymentInstructions,
                    'paid_at' => $paymentStatus === 'paid' ? now() : $session->paid_at,
                ])->save();

                if ($paymentStatus === 'paid' && ! $session->created_order_id) {
                    $order = $finalizer->finalizePaidSession($session);

                    $attempt->forceFill([
                        'order_id' => $order->id,
                    ])->save();
                }

                return;
            }

            if ($order) {
                $order->forceFill([
                    'payment_status' => $paymentStatus,
                    'status' => $orderStatus,
                    'payment_gateway' => 'midtrans',
                    'payment_method' => $payload['payment_type'] ?? $order->payment_method,
                    'midtrans_order_id' => $gatewayOrderId,
                    'midtrans_transaction_id' => $payload['transaction_id'] ?? $order->midtrans_transaction_id,
                    'midtrans_payment_type' => $payload['payment_type'] ?? $order->midtrans_payment_type,
                    'midtrans_transaction_status' => $transactionStatus,
                    'midtrans_fraud_status' => $fraudStatus,
                    'midtrans_payload' => $payload,
                    'payment_instructions' => $paymentInstructions,
                    'paid_at' => $paymentStatus === 'paid' ? now() : $order->paid_at,
                ])->save();
            }
        });

        return response()->json(['message' => 'OK']);
    }

    private function isValidSignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('midtrans.server_key');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    private function mapStatuses(string $transactionStatus, ?string $fraudStatus, string $currentOrderStatus): array
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept'
                ? ['paid', 'confirmed']
                : ['pending', $currentOrderStatus],

            'settlement' => ['paid', 'confirmed'],

            'pending' => ['pending', 'pending'],

            'deny', 'failure' => ['failed', 'cancelled'],

            'expire' => ['expired', 'cancelled'],

            'cancel' => ['cancelled', 'cancelled'],

            'refund' => ['refunded', $currentOrderStatus],

            default => ['pending', $currentOrderStatus],
        };
    }

    private function mapSessionStatus(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'paid',
            'failed' => 'failed',
            'expired' => 'expired',
            'cancelled' => 'cancelled',
            default => 'pending_payment',
        };
    }

    private function mapAttemptStatus(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'paid',
            'failed' => 'failed',
            'expired' => 'expired',
            'refunded' => 'refunded',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    private function extractPaymentInstructions(array $payload): array
    {
        return array_filter([
            'va_numbers' => $payload['va_numbers'] ?? null,
            'permata_va_number' => $payload['permata_va_number'] ?? null,
            'biller_code' => $payload['biller_code'] ?? null,
            'bill_key' => $payload['bill_key'] ?? null,
            'payment_code' => $payload['payment_code'] ?? null,
            'store' => $payload['store'] ?? null,
            'bank' => $payload['bank'] ?? null,
            'pdf_url' => $payload['pdf_url'] ?? null,
        ]);
    }
}
