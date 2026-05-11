<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class FinalizeCheckoutSessionService
{
    public function finalizePaidSession(CheckoutSession $session, ?string $changedBy = null): Order
    {
        return DB::transaction(function () use ($session, $changedBy): Order {
            /** @var CheckoutSession $lockedSession */
            $lockedSession = CheckoutSession::query()
                ->with(['items', 'paymentAttempts'])
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->created_order_id) {
                return Order::query()->findOrFail($lockedSession->created_order_id);
            }

            if ($lockedSession->status !== 'paid') {
                throw new RuntimeException('Checkout session belum paid.');
            }

            $order = Order::query()->create([
                'order_number' => $this->makeOrderNumber(),
                'midtrans_order_id' => $lockedSession->midtrans_order_id,
                'user_id' => $lockedSession->user_id,

                'status' => 'confirmed',
                'payment_status' => 'paid',

                'currency' => $lockedSession->currency,
                'subtotal' => $lockedSession->subtotal,
                'shipping_cost' => $lockedSession->shipping_cost,
                'discount_total' => $lockedSession->discount_total,
                'tax_total' => $lockedSession->tax_total,
                'grand_total' => $lockedSession->grand_total,

                'shipping_address' => $lockedSession->shipping_address,
                'payment_method' => $lockedSession->payment_method,
                'payment_gateway' => $lockedSession->payment_gateway,

                'midtrans_transaction_id' => $lockedSession->midtrans_transaction_id,
                'midtrans_snap_token' => $lockedSession->midtrans_snap_token,
                'midtrans_redirect_url' => $lockedSession->midtrans_redirect_url,
                'midtrans_payment_type' => $lockedSession->midtrans_payment_type,
                'midtrans_transaction_status' => $lockedSession->midtrans_transaction_status,
                'midtrans_fraud_status' => $lockedSession->midtrans_fraud_status,
                'midtrans_payload' => $lockedSession->midtrans_payload,
                'payment_instructions' => $lockedSession->payment_instructions,

                'paid_at' => $lockedSession->paid_at ?: now(),
                'payment_expires_at' => $lockedSession->expires_at,
                'notes' => $lockedSession->notes,
            ]);

            foreach ($lockedSession->items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'currency' => $item->currency,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $lockedSession->paymentAttempts()->update([
                'order_id' => $order->id,
            ]);

            $lockedSession->forceFill([
                'created_order_id' => $order->id,
                'paid_at' => $lockedSession->paid_at ?: now(),
            ])->save();

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'confirmed',
                'note' => 'Order dibuat otomatis dari checkout session yang sudah paid.',
                'changed_by' => $changedBy,
            ]);

            /*
             * TODO: kosongkan cart user di sini.
             * Sesuaikan dengan struktur cart kamu.
             *
             * Contoh:
             * Cart::query()->where('user_id', $lockedSession->user_id)->delete();
             */

            return $order;
        });
    }

    private function makeOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }
}


