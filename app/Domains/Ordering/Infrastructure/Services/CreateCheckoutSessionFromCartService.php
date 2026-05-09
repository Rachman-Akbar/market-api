<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use App\Domains\Ordering\Domain\Repositories\CartForOrderReaderInterface;
use App\Models\CheckoutSession;
use App\Models\CheckoutSessionItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class CreateCheckoutSessionFromCartService
{
    public function __construct(
        private readonly CartForOrderReaderInterface $cartReader,
    ) {
    }

    public function create(array $payload, User $user, ?UploadedFile $transferProof = null): CheckoutSession
    {
        return DB::transaction(function () use ($payload, $user, $transferProof): CheckoutSession {
            $userId = (string) $user->getAuthIdentifier();
            $paymentMethod = (string) ($payload['payment_method'] ?? '');

            $cart = $this->cartReader->getActiveCartForUser($userId);

            if ($cart === null || empty($cart['items'])) {
                throw new RuntimeException('Keranjang masih kosong.');
            }

            $cartItems = array_values(array_map(
                static function (array $item): array {
                    $quantity = max(0, (int) ($item['quantity'] ?? 0));
                    $unitPrice = (float) ($item['unit_price'] ?? 0);

                    return [
                        'cart_item_id' => (int) ($item['id'] ?? 0),
                        'product_id' => (int) ($item['product_id'] ?? 0),
                        'product_name' => (string) ($item['product_name'] ?? 'Product'),
                        'sku' => $item['sku'] ?? null,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $quantity * $unitPrice,
                    ];
                },
                $cart['items'],
            ));

            $cartItems = array_values(array_filter(
                $cartItems,
                static fn (array $item): bool => $item['quantity'] > 0 && $item['product_id'] > 0,
            ));

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
            $manualTransferPayload = null;

            if ($paymentMethod === 'manual_transfer') {
                $manualTransferPayload = Arr::except(
                    $payload['manual_transfer'] ?? [],
                    ['transfer_proof'],
                );

                if ($manualTransferPayload === []) {
                    $manualTransferPayload = null;
                }

                if ($transferProof instanceof UploadedFile) {
                    $proofPath = $transferProof->store('manual-transfer-proofs', 'public');
                }
            }

            $session = CheckoutSession::query()->create([
                'session_number' => $this->makeSessionNumber(),
                'user_id' => $userId,

                'status' => $paymentMethod === 'manual_transfer'
                    ? 'waiting_manual_verification'
                    : 'pending_payment',

                'payment_gateway' => $paymentMethod === 'midtrans'
                    ? 'midtrans'
                    : null,

                'payment_method' => $paymentMethod,

                'currency' => 'IDR',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,

                'cart_snapshot' => $cartItems,
                'shipping_address' => $payload['shipping_address'],
                'notes' => $payload['notes'] ?? null,

                'manual_transfer_payload' => $manualTransferPayload,
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