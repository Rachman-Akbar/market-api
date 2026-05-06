<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;
use Throwable;

final class MidtransSnapService
{
    /**
     * Membuat transaksi Snap Midtrans untuk sebuah order.
     *
     * Konsep:
     * - orders.payment_gateway = midtrans
     * - orders.payment_method = null sampai user memilih metode di Snap
     * - orders.payment_status = pending setelah Snap token berhasil dibuat
     * - payment_attempts menyimpan setiap percobaan pembayaran
     */
    public function createTransaction(Order $order, User $user): array
    {
        $this->configure();

        $attempt = DB::transaction(function () use ($order): PaymentAttempt {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status === 'paid') {
                throw new RuntimeException('Order ini sudah dibayar.');
            }

            if ($lockedOrder->status === 'cancelled') {
                throw new RuntimeException('Order sudah dibatalkan dan tidak bisa dibayar.');
            }

            if (in_array($lockedOrder->payment_status, ['cancelled', 'refunded'], true)) {
                throw new RuntimeException('Order ini tidak bisa dibayar dengan status pembayaran saat ini.');
            }

            $grossAmount = $this->moneyToInt($lockedOrder->grand_total);

            if ($grossAmount <= 0) {
                throw new RuntimeException('Total pembayaran tidak valid.');
            }

            /**
             * Reuse attempt aktif agar user tidak membuat transaksi Midtrans
             * berkali-kali untuk order yang sama.
             */
            $activeAttempt = PaymentAttempt::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('status', ['initiated', 'pending'])
                ->latest('id')
                ->first();

            if ($activeAttempt) {
                $expiresAt = $activeAttempt->expires_at
                    ? Carbon::parse($activeAttempt->expires_at)
                    : null;

                $isExpired = $expiresAt !== null && $expiresAt->isPast();

                if ($isExpired) {
                    $activeAttempt->forceFill([
                        'status' => 'expired',
                        'expired_at' => now(),
                        'failure_reason' => 'Payment attempt expired.',
                    ])->save();

                    $lockedOrder->forceFill([
                        'payment_status' => 'expired',
                        'payment_expires_at' => $activeAttempt->expires_at,
                    ])->save();
                } elseif ($activeAttempt->snap_token) {
                    return $activeAttempt;
                } elseif ($activeAttempt->created_at && $activeAttempt->created_at->gt(now()->subMinutes(2))) {
                    throw new RuntimeException('Transaksi pembayaran sedang dibuat. Silakan refresh halaman pembayaran.');
                } else {
                    $activeAttempt->forceFill([
                        'status' => 'failed',
                        'failure_reason' => 'Snap token was not created after waiting period.',
                    ])->save();
                }
            }

            /**
             * Karena order row sudah lockForUpdate, attempt number aman dari race condition
             * untuk order yang sama.
             */
            $attemptNumber = PaymentAttempt::query()
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->count() + 1;

            $gatewayOrderId = $this->makeGatewayOrderId(
                $lockedOrder->order_number,
                $attemptNumber,
            );

            return PaymentAttempt::query()->create([
                'order_id' => $lockedOrder->id,
                'attempt_no' => $attemptNumber,
                'gateway' => 'midtrans',
                'gateway_order_id' => $gatewayOrderId,
                'status' => 'initiated',
                'currency' => $lockedOrder->currency ?: 'IDR',
                'gross_amount' => $grossAmount,
                'expires_at' => now()->addDay(),
            ]);
        });

        if ($attempt->snap_token) {
            return [
                'order_id' => $attempt->gateway_order_id,
                'snap_token' => $attempt->snap_token,
                'redirect_url' => $attempt->redirect_url,
            ];
        }

        $order->refresh();
        $order->loadMissing(['items']);

       $params = $this->buildSnapParams(
    order: $order,
    user: $user,
    gatewayOrderId: $attempt->gateway_order_id,
);

        try {
            $transaction = Snap::createTransaction($params);
        } catch (Throwable $exception) {
            $attempt->forceFill([
                'status' => 'failed',
                'request_payload' => $params,
                'failure_reason' => $exception->getMessage(),
                'latest_notification_payload' => [
                    'source' => 'create_snap_transaction',
                    'error' => $exception->getMessage(),
                ],
            ])->save();

            throw $exception;
        }

        $transactionPayload = json_decode(json_encode($transaction), true);

        if (empty($transaction->token)) {
            $attempt->forceFill([
                'status' => 'failed',
                'request_payload' => $params,
                'response_payload' => $transactionPayload,
                'failure_reason' => 'Midtrans tidak mengembalikan Snap token.',
            ])->save();

            throw new RuntimeException('Midtrans tidak mengembalikan Snap token.');
        }

        DB::transaction(function () use ($attempt, $order, $params, $transaction, $transactionPayload): void {
            $expiresAt = $attempt->expires_at ?: now()->addDay();

            $attempt->forceFill([
                'status' => 'pending',
                'snap_token' => $transaction->token ?? null,
                'redirect_url' => $transaction->redirect_url ?? null,
                'request_payload' => $params,
                'response_payload' => $transactionPayload,
                'expires_at' => $expiresAt,
            ])->save();

            /**
             * payment_method sengaja NULL.
             *
             * Karena "midtrans" adalah gateway, bukan metode pembayaran.
             * Metode asli baru diketahui dari webhook:
             * bank_transfer / qris / gopay / shopeepay / credit_card / dll.
             */
            $order->forceFill([
                'payment_gateway' => 'midtrans',
                'payment_method' => null,
                'payment_status' => 'pending',

                'midtrans_order_id' => $attempt->gateway_order_id,
                'midtrans_transaction_id' => null,
                'midtrans_snap_token' => $transaction->token ?? null,
                'midtrans_redirect_url' => $transaction->redirect_url ?? null,
                'midtrans_payment_type' => null,
                'midtrans_transaction_status' => 'pending',
                'midtrans_fraud_status' => null,
                'midtrans_payload' => $params,

                'payment_instructions' => null,
                'payment_failed_reason' => null,
                'payment_expires_at' => $expiresAt,
            ])->save();
        });

        return [
            'order_id' => $attempt->gateway_order_id,
            'snap_token' => $transaction->token ?? null,
            'redirect_url' => $transaction->redirect_url ?? null,
        ];
    }

    private function buildSnapParams(Order $order, User $user, string $gatewayOrderId): array
    {
        $grossAmount = $this->moneyToInt($order->grand_total);

        if ($grossAmount <= 0) {
            throw new RuntimeException('Total pembayaran tidak valid.');
        }

        $itemDetails = $this->buildItemDetails($order, $grossAmount);
        $shippingAddress = $this->normalizeShippingAddress($order->shipping_address);

        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        $payload = [
            'transaction_details' => [
                'order_id' => $gatewayOrderId,
                'gross_amount' => $grossAmount,
            ],

            /**
             * Ini letak yang benar.
             * Jangan dikirim sebagai argument ke buildSnapParams().
             */
            'credit_card' => [
                'secure' => true,
            ],

            'item_details' => $itemDetails,

            'customer_details' => [
                'first_name' => $this->limitString(
                    $shippingAddress['recipient_name'] ?? $user->name ?? 'Customer',
                    255,
                ),
                'email' => $user->email,
                'phone' => $shippingAddress['phone'] ?? null,

                'shipping_address' => [
                    'first_name' => $this->limitString(
                        $shippingAddress['recipient_name'] ?? $user->name ?? 'Customer',
                        255,
                    ),
                    'phone' => $shippingAddress['phone'] ?? null,
                    'address' => $this->limitString(
                        $shippingAddress['address_line'] ?? '',
                        255,
                    ),
                    'city' => $this->limitString(
                        $shippingAddress['city'] ?? '',
                        50,
                    ),
                    'postal_code' => $this->limitString(
                        $shippingAddress['postal_code'] ?? '',
                        10,
                    ),
                    'country_code' => 'IDN',
                ],
            ],

            'callbacks' => [
                'finish' => $frontendUrl
                    . '/orders/payments?order=' . urlencode($order->order_number)
                    . '&state=pending',
            ],

            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'day',
                'duration' => 1,
            ],
        ];

        $enabledPayments = config('midtrans.enabled_payments');

        if (is_array($enabledPayments) && $enabledPayments !== []) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        return $payload;
    }

    /**
     * item_details harus totalnya sama persis dengan gross_amount.
     */
    private function buildItemDetails(Order $order, int $grossAmount): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $unitPrice = $this->moneyToInt($item->unit_price);
            $quantity = (int) $item->quantity;

            if ($unitPrice <= 0 || $quantity <= 0) {
                continue;
            }

            $items[] = [
                'id' => $this->limitString((string) ($item->sku ?: $item->product_id), 50),
                'price' => $unitPrice,
                'quantity' => $quantity,
                'name' => $this->limitString((string) $item->product_name, 50),
            ];
        }

        $shippingCost = $this->moneyToInt($order->shipping_cost);
        $taxTotal = $this->moneyToInt($order->tax_total);
        $discountTotal = $this->moneyToInt($order->discount_total);

        if ($shippingCost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => $shippingCost,
                'quantity' => 1,
                'name' => 'Shipping Cost',
            ];
        }

        if ($taxTotal > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => $taxTotal,
                'quantity' => 1,
                'name' => 'Tax',
            ];
        }

        if ($discountTotal > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -1 * $discountTotal,
                'quantity' => 1,
                'name' => 'Discount',
            ];
        }

        if ($items === []) {
            throw new RuntimeException('Order tidak memiliki item pembayaran yang valid.');
        }

        $itemTotal = array_reduce(
            $items,
            static fn (int $carry, array $item): int => $carry + ((int) $item['price'] * (int) $item['quantity']),
            0,
        );

        $difference = $grossAmount - $itemTotal;

        if ($difference !== 0) {
            $items[] = [
                'id' => 'ADJUSTMENT',
                'price' => $difference,
                'quantity' => 1,
                'name' => 'Order Adjustment',
            ];

            $itemTotal += $difference;
        }

        if ($itemTotal !== $grossAmount) {
            throw new RuntimeException(
                "Total item_details ({$itemTotal}) tidak sama dengan gross_amount ({$grossAmount}).",
            );
        }

        return $items;
    }

    private function normalizeShippingAddress(mixed $shippingAddress): array
    {
        if (is_array($shippingAddress)) {
            return $shippingAddress;
        }

        if (is_string($shippingAddress)) {
            $decoded = json_decode($shippingAddress, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            return [
                'recipient_name' => null,
                'phone' => null,
                'address_line' => $shippingAddress,
                'city' => null,
                'postal_code' => null,
            ];
        }

        return [];
    }

    private function makeGatewayOrderId(string $orderNumber, int $attemptNumber): string
    {
        $safeOrderNumber = preg_replace('/[^A-Za-z0-9._~-]/', '-', $orderNumber) ?: $orderNumber;

        $suffix = '-PAY-' . $attemptNumber . '-' . Str::upper(Str::random(6));

        $maxLength = 50;
        $prefixLength = max(1, $maxLength - strlen($suffix));

        return substr($safeOrderNumber, 0, $prefixLength) . $suffix;
    }

    private function moneyToInt(mixed $value): int
    {
        return (int) round((float) $value);
    }

    private function limitString(?string $value, int $limit): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return Str::limit($value, $limit, '');
    }

    private function configure(): void
    {
        $serverKey = config('midtrans.server_key');

        if (! is_string($serverKey) || trim($serverKey) === '') {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('midtrans.is_production', false);
        Config::$isSanitized = (bool) config('midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('midtrans.is_3ds', true);
    }
}
