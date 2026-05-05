<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;
use Throwable;

final class MidtransSnapService
{
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

            $activeAttempt = PaymentAttempt::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('status', ['initiated', 'pending'])
                ->latest('id')
                ->first();

            if ($activeAttempt && $activeAttempt->snap_token) {
                return $activeAttempt;
            }

            $attemptNumber = PaymentAttempt::query()
                ->where('order_id', $lockedOrder->id)
                ->count() + 1;

            $gatewayOrderId = $this->makeGatewayOrderId(
                $lockedOrder->order_number,
                $attemptNumber
            );

            return PaymentAttempt::query()->create([
                'order_id' => $lockedOrder->id,
                'gateway' => 'midtrans',
                'gateway_order_id' => $gatewayOrderId,
                'status' => 'initiated',
                'currency' => 'IDR',
                'gross_amount' => $this->moneyToInt($lockedOrder->grand_total),
            ]);
        });

        if ($attempt->snap_token) {
            return [
                'order_id' => $attempt->gateway_order_id,
                'snap_token' => $attempt->snap_token,
                'redirect_url' => $attempt->redirect_url,
            ];
        }

        $order->loadMissing(['items']);

        $params = $this->buildSnapParams($order, $user, $attempt->gateway_order_id);

        try {
            $transaction = Snap::createTransaction($params);
        } catch (Throwable $e) {
            $attempt->forceFill([
                'status' => 'failed',
                'request_payload' => $params,
                'latest_notification_payload' => [
                    'source' => 'create_snap_transaction',
                    'error' => $e->getMessage(),
                ],
            ])->save();

            throw $e;
        }

        $attempt->forceFill([
            'status' => 'pending',
            'snap_token' => $transaction->token ?? null,
            'redirect_url' => $transaction->redirect_url ?? null,
            'request_payload' => $params,
            'response_payload' => json_decode(json_encode($transaction), true),
        ])->save();

        $order->forceFill([
            'payment_gateway' => 'midtrans',
            'payment_method' => null,
            'payment_status' => 'pending',
            'midtrans_order_id' => $attempt->gateway_order_id,
            'midtrans_snap_token' => $transaction->token ?? null,
            'midtrans_redirect_url' => $transaction->redirect_url ?? null,
            'midtrans_payload' => $params,
        ])->save();

        return [
            'order_id' => $attempt->gateway_order_id,
            'snap_token' => $transaction->token ?? null,
            'redirect_url' => $transaction->redirect_url ?? null,
        ];
    }

    private function buildSnapParams(Order $order, User $user, string $gatewayOrderId): array
    {
        $grossAmount = $this->moneyToInt($order->grand_total);
        $itemDetails = $this->buildItemDetails($order, $grossAmount);

        $shippingAddress = is_array($order->shipping_address)
            ? $order->shipping_address
            : [];

        return [
            'transaction_details' => [
                'order_id' => $gatewayOrderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $shippingAddress['recipient_name'] ?? $user->name ?? 'Customer',
                'email' => $user->email,
                'phone' => $shippingAddress['phone'] ?? null,
                'shipping_address' => [
                    'first_name' => $shippingAddress['recipient_name'] ?? $user->name ?? 'Customer',
                    'phone' => $shippingAddress['phone'] ?? null,
                    'address' => $shippingAddress['address_line'] ?? null,
                    'city' => $shippingAddress['city'] ?? null,
                    'postal_code' => $shippingAddress['postal_code'] ?? null,
                    'country_code' => 'IDN',
                ],
            ],
            'callbacks' => [
                'finish' => rtrim((string) config('app.frontend_url'), '/')
                    . '/orders/success?order=' . urlencode($order->order_number),
            ],
        ];
    }

    private function buildItemDetails(Order $order, int $grossAmount): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) ($item->sku ?: $item->product_id),
                'price' => $this->moneyToInt($item->unit_price),
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($item->product_name, 50, ''),
            ];
        }

        if ($this->moneyToInt($order->shipping_cost) > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => $this->moneyToInt($order->shipping_cost),
                'quantity' => 1,
                'name' => 'Shipping Cost',
            ];
        }

        if ($this->moneyToInt($order->tax_total) > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => $this->moneyToInt($order->tax_total),
                'quantity' => 1,
                'name' => 'Tax',
            ];
        }

        if ($this->moneyToInt($order->discount_total) > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -1 * $this->moneyToInt($order->discount_total),
                'quantity' => 1,
                'name' => 'Discount',
            ];
        }

        $sum = array_reduce(
            $items,
            fn (int $carry, array $item): int => $carry + ((int) $item['price'] * (int) $item['quantity']),
            0
        );

        if ($sum !== $grossAmount) {
            throw new RuntimeException(
                "Total item_details ({$sum}) tidak sama dengan gross_amount ({$grossAmount})."
            );
        }

        return $items;
    }

    private function makeGatewayOrderId(string $orderNumber, int $attemptNumber): string
    {
        $safeOrderNumber = preg_replace('/[^A-Za-z0-9._~-]/', '-', $orderNumber);

        return substr($safeOrderNumber . '-PAY-' . $attemptNumber, 0, 50);
    }

    private function moneyToInt(mixed $value): int
    {
        return (int) round((float) $value);
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