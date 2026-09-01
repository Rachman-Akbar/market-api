<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\UseCases;

use App\Domains\Order\Payment\Infrastructure\Services\MidtransService;
use App\Domains\PPOB\Application\Services\PricingEngine;
use App\Domains\PPOB\Domain\Entities\PpoTransaction;
use App\Domains\PPOB\Domain\Entities\PpoTransactionStatus;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handles placing a prepaid PPOB order. A Midtrans Snap token is created and
 * the transaction is held in a "pending payment" state until paid; the actual
 * provider top-up is submitted after payment is confirmed.
 */
class PlacePpoOrderUseCase
{
    public function __construct(
        private PpoProductRepositoryInterface $products,
        private PpoTransactionRepositoryInterface $transactions,
        private PricingEngine $pricing,
        private MidtransService $midtrans,
    ) {}

    /**
     * @return array{transaction: PpoTransactionModel|PpoTransaction, status: string, message: string, is_new: bool, snap_token: ?string, total_amount: float}
     */
    public function execute(string $userId, int $productId, string $customerId, ?string $customerName = null, ?string $customerEmail = null): array
    {
        $product = $this->products->findById($productId);

        if (! $product || ! $product->isAvailable || $product->status !== 'active' || ! $product->isActive) {
            throw new \RuntimeException('Produk tidak tersedia.', 404);
        }

        if ((string) config('midtrans.server_key') === '') {
            throw new \RuntimeException('Payment gateway belum dikonfigurasi.', 422);
        }

        $priced = $this->pricing->priceProduct($product);
        $breakdown = $this->pricing->buildBreakdown($priced['product']);

        $referenceId = $this->generateReferenceId();

        $snapToken = $this->midtrans->createSnapToken([
            'order_id' => $referenceId,
            'gross_amount' => (int) round($breakdown['total_amount']),
            'user_id' => $userId,
            'customer_name' => $customerName ?? 'Customer',
            'customer_email' => $customerEmail ?? '',
        ]);

        $transaction = DB::transaction(function () use ($userId, $product, $customerId, $referenceId, $breakdown, $snapToken) {
            $tx = PpoTransactionModel::create([
                'reference_id' => $referenceId,
                'user_id' => $userId,
                'operator_id' => $product->operatorId,
                'product_id' => $product->id,
                'provider_product_code' => $product->providerProductCode,
                'product_name' => $product->name,
                'category' => $product->category,
                'product_type' => $product->productType,
                'customer_id' => $customerId,
                'provider_price' => $breakdown['provider_price'],
                'admin_fee' => $breakdown['admin_fee'],
                'commission' => $breakdown['commission'],
                'margin' => $breakdown['margin'],
                'revenue' => $breakdown['revenue'],
                'net_profit' => $breakdown['net_profit'],
                'total_amount' => $breakdown['total_amount'],
                'payment_method' => 'midtrans',
                'payment_status' => 'pending',
                'midtrans_snap_token' => $snapToken,
                'status' => PpoTransactionStatus::Pending->value,
                'created_by' => $userId,
                'updated_by' => $userId,
                'expires_at' => now()->addMinutes((int) config('ppob.pending_expiry_minutes', 15)),
            ]);

            return $tx;
        });

        return [
            'transaction' => $transaction->fresh(),
            'status' => $transaction->status,
            'message' => 'Silakan selesaikan pembayaran Anda.',
            'is_new' => true,
            'snap_token' => $snapToken,
            'total_amount' => (float) $breakdown['total_amount'],
        ];
    }

    private function generateReferenceId(): string
    {
        $prefix = (string) config('ppob.reference_prefix', 'PPOB');

        return $prefix.date('ymdHis').strtoupper(Str::random(6));
    }
}
