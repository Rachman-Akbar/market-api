<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Models\Order;
use App\Models\User;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

final class MidtransSnapService
{
    public function createTransaction(Order $order, User $user): array
    {
        $this->configure();

        $grossAmount = (int) round((float) $order->grand_total);

        if ($grossAmount <= 0) {
            throw new RuntimeException('Total pembayaran tidak valid.');
        }

        $midtransOrderId = $order->midtrans_order_id ?: $order->order_number;

        $shippingAddress = is_array($order->shipping_address)
            ? $order->shipping_address
            : [];

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id' => $order->order_number,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Order '.$order->order_number,
                ],
            ],
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
                'finish' => rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/')
                    .'/orders/success?order='.urlencode($order->order_number),
            ],
        ];

        $transaction = Snap::createTransaction($params);

        $order->forceFill([
            'payment_method' => 'midtrans',
            'payment_status' => 'pending',
            'midtrans_order_id' => $midtransOrderId,
            'midtrans_snap_token' => $transaction->token ?? null,
            'midtrans_redirect_url' => $transaction->redirect_url ?? null,
        ])->save();

        return [
            'order_id' => $midtransOrderId,
            'snap_token' => $transaction->token ?? null,
            'redirect_url' => $transaction->redirect_url ?? null,
        ];
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
