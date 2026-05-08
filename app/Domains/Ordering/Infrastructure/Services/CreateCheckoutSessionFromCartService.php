<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Models\CheckoutSession;
use App\Models\CheckoutSessionItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class CreateCheckoutSessionFromCartService
{
    public function create(array $payload, User $user, ?UploadedFile $transferProof = null): CheckoutSession
    {
        return DB::transaction(function () use ($payload, $user, $transferProof): CheckoutSession {
            /*
             * TODO:
             * Ganti bagian ini dengan cart reader kamu.
             *
             * Target hasil:
             * $cartItems = [
             *   [
             *     'product_id' => 1,
             *     'product_name' => 'Nama Produk',
             *     'sku' => 'SKU-1',
             *     'quantity' => 2,
             *     'unit_price' => 10000,
             *     'subtotal' => 20000,
             *   ],
             * ];
             */
            $cartItems = [];

            if ($cartItems === []) {
                throw new RuntimeException('Keranjang masih kosong.');
            }

            $subtotal = array_reduce(
                $cartItems,
                static fn (float $carry, array $item): float => $carry + (float) $item['subtotal'],
                0.0,
            );

            $shippingCost = 0.0;
            $discountTotal = 0.0;
            $taxTotal = 0.0;
            $grandTotal = $subtotal + $shippingCost + $taxTotal - $discountTotal;

            $proofPath = null;

            if (($payload['payment_method'] ?? null) === 'manual_transfer' && $transferProof) {
                $proofPath = $transferProof->store('manual-transfer-proofs', 'public');
            }

            $session = CheckoutSession::query()->create([
                'session_number' => $this->makeSessionNumber(),
                'user_id' => (string) $user->getAuthIdentifier(),

                'status' => $payload['payment_method'] === 'manual_transfer'
                    ? 'waiting_manual_verification'
                    : 'draft',

                'payment_gateway' => $payload['payment_method'] === 'midtrans'
                    ? 'midtrans'
                    : null,

                'payment_method' => (string) $payload['payment_method'],

                'currency' => 'IDR',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,

                'cart_snapshot' => $cartItems,
                'shipping_address' => $payload['shipping_address'],
                'notes' => $payload['notes'] ?? null,

                'manual_transfer_payload' => $payload['manual_transfer'] ?? null,
                'manual_transfer_proof_path' => $proofPath,
                'expires_at' => now()->addDay(),
            ]);

            foreach ($cartItems as $item) {
                CheckoutSessionItem::query()->create([
                    'checkout_session_id' => $session->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'currency' => 'IDR',
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $session->load('items');
        });
    }

    private function makeSessionNumber(): string
    {
        return 'CHK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }
}
