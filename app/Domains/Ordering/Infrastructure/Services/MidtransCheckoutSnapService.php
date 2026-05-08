<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;
use Throwable;

final class MidtransCheckoutSessionSnapService
{
    public function createTransaction(CheckoutSession $session, User $user): array
    {
        $this->configure();

        $attempt = DB::transaction(function () use ($session): PaymentAttempt {
            /** @var CheckoutSession $lockedSession */
            $lockedSession = CheckoutSession::query()
                ->with('items')
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->created_order_id) {
                throw new RuntimeException('Checkout session ini sudah menjadi order.');
            }

            if (! in_array($lockedSession->status, ['draft', 'pending_payment'], true)) {
                throw new RuntimeException('Checkout session tidak bisa dibayar.');
            }

            if ($lockedSession->payment_method !== 'midtrans') {
                throw new RuntimeException('Checkout session ini bukan pembayaran Midtrans.');
            }

            $grossAmount = $this->moneyToInt($lockedSession->grand_total);

            if ($grossAmount <= 0) {
                throw new RuntimeException('Total pembayaran tidak valid.');
            }

            $activeAttempt = PaymentAttempt::query()
                ->where('checkout_session_id', $lockedSession->id)
                ->whereIn('status', ['initiated', 'pending'])
                ->latest('id')
                ->first();

            if ($activeAttempt) {
                $expiresAt = $activeAttempt->expires_at
                    ? Carbon::parse($activeAttempt->expires_at)
                    : null;

                if ($expiresAt && $expiresAt->isPast()) {
                    $activeAttempt->forceFill([
                        'status' => 'expired',
                    ])->save();

                    $lockedSession->forceFill([
                        'status' => 'expired',
                        'expires_at' => $activeAttempt->expires_at,
                    ])->save();
                } elseif ($activeAttempt->snap_token) {
                    return $activeAttempt;
                }
            }

            $attemptNumber = PaymentAttempt::query()
                ->where('checkout_session_id', $lockedSession->id)
                ->lockForUpdate()
                ->count() + 1;

            $gatewayOrderId = $this->makeGatewayOrderId(
                $lockedSession->session_number,
                $attemptNumber,
            );

            return PaymentAttempt::query()->create([
                'order_id' => null,
                'checkout_session_id' => $lockedSession->id,
                'attempt_no' => $attemptNumber,
                'gateway' => 'midtrans',
                'gateway_order_id' => $gatewayOrderId,
                'status' => 'initiated',
                'currency' => $lockedSession->currency ?: 'IDR',
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

        $session->refresh();
        $session->loadMissing('items');

        $params = $this->buildSnapParams($session, $user, $attempt->gateway_order_id);

        try {
            $transaction = Snap::createTransaction($params);
        } catch (Throwable $exception) {
            $attempt->forceFill([
                'status' => 'failed',
                'request_payload' => $params,
                'failure_reason' => $exception->getMessage(),
                'latest_notification_payload' => [
                    'source' => 'create_checkout_session_snap_transaction',
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

        DB::transaction(function () use ($session, $attempt, $params, $transaction, $transactionPayload): void {
            $expiresAt = $attempt->expires_at ?: now()->addDay();

            $attempt->forceFill([
                'status' => 'pending',
                'snap_token' => $transaction->token ?? null,
                'redirect_url' => $transaction->redirect_url ?? null,
                'request_payload' => $params,
                'response_payload' => $transactionPayload,
                'expires_at' => $expiresAt,
            ])->save();

            $session->forceFill([
                'status' => 'pending_payment',
                'payment_gateway' => 'midtrans',
                'payment_method' => 'midtrans',

                'midtrans_order_id' => $attempt->gateway_order_id,
                'midtrans_transaction_id' => null,
                'midtrans_snap_token' => $transaction->token ?? null,
                'midtrans_redirect_url' => $transaction->redirect_url ?? null,
                'midtrans_payment_type' => null,
                'midtrans_transaction_status' => 'pending',
                'midtrans_fraud_status' => null,
                'midtrans_payload' => $params,
                'payment_instructions' => null,
                'expires_at' => $expiresAt,
            ])->save();
        });

        return [
            'order_id' => $attempt->gateway_order_id,
            'snap_token' => $transaction->token ?? null,
            'redirect_url' => $transaction->redirect_url ?? null,
        ];
    }

    private function buildSnapParams(CheckoutSession $session, User $user, string $gatewayOrderId): array
    {
        $grossAmount = $this->moneyToInt($session->grand_total);

        if ($grossAmount <= 0) {
            throw new RuntimeException('Total pembayaran tidak valid.');
        }

        $itemDetails = $this->buildItemDetails($session, $grossAmount);
        $shippingAddress = is_array($session->shipping_address)
            ? $session->shipping_address
            : [];

        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        $payload = [
            'transaction_details' => [
                'order_id' => $gatewayOrderId,
                'gross_amount' => $grossAmount,
            ],

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
                    'address' => $this->limitString($shippingAddress['address_line'] ?? '', 255),
                    'city' => $this->limitString($shippingAddress['city'] ?? '', 50),
                    'postal_code' => $this->limitString($shippingAddress['postal_code'] ?? '', 10),
                    'country_code' => 'IDN',
                ],
            ],

            'callbacks' => [
                'finish' => $frontendUrl
                    . '/orders/payment?session=' . urlencode($session->session_number)
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

    private function buildItemDetails(CheckoutSession $session, int $grossAmount): array
    {
        $items = [];

        foreach ($session->items as $item) {
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

        $shippingCost = $this->moneyToInt($session->shipping_cost);
        $taxTotal = $this->moneyToInt($session->tax_total);
        $discountTotal = $this->moneyToInt($session->discount_total);

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
            throw new RuntimeException('Checkout session tidak memiliki item pembayaran yang valid.');
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

    private function makeGatewayOrderId(string $sessionNumber, int $attemptNumber): string
    {
        $safeSessionNumber = preg_replace('/[^A-Za-z0-9._~-]/', '-', $sessionNumber) ?: $sessionNumber;
        $suffix = '-PAY-' . $attemptNumber . '-' . Str::upper(Str::random(6));
        $prefixLength = max(1, 50 - strlen($suffix));

        return substr($safeSessionNumber, 0, $prefixLength) . $suffix;
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
