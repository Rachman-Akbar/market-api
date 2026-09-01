<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\UseCases;

use App\Domains\PPOB\Application\Services\IakProviderService;
use App\Domains\PPOB\Application\Services\PpoFinanceService;
use App\Domains\PPOB\Application\Services\PricingEngine;
use App\Domains\PPOB\Domain\Entities\PpoTransaction;
use App\Domains\PPOB\Domain\Entities\PpoTransactionStatus;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handles placing a prepaid PPOB order and advancing it through the provider
 * lifecycle with a unique idempotency reference.
 */
class PlacePpoOrderUseCase
{
    public function __construct(
        private PpoProductRepositoryInterface $products,
        private PpoTransactionRepositoryInterface $transactions,
        private PricingEngine $pricing,
        private IakProviderService $provider,
        private PpoFinanceService $finance,
    ) {}

    /**
     * @return array{transaction: PpoTransactionModel|PpoTransaction, status: string, message: string, is_new: bool}
     */
    public function execute(string $userId, int $productId, string $customerId): array
    {
        $product = $this->products->findById($productId);

        if (! $product || ! $product->isAvailable || $product->status !== 'active' || ! $product->isActive) {
            throw new \RuntimeException('Produk tidak tersedia.', 404);
        }

        $priced = $this->pricing->priceProduct($product);
        $breakdown = $this->pricing->buildBreakdown($priced['product']);

        $referenceId = $this->generateReferenceId();

        $transaction = DB::transaction(function () use ($userId, $product, $customerId, $referenceId, $breakdown) {
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
                'status' => PpoTransactionStatus::Pending->value,
                'created_by' => $userId,
                'updated_by' => $userId,
                'expires_at' => now()->addMinutes((int) config('ppob.pending_expiry_minutes', 15)),
            ]);

            return $tx;
        });

        // Submit to IAK outside the transaction lock (network I/O).
        $result = $this->provider->submitTopUp(
            $referenceId,
            $customerId,
            $product->providerProductCode,
            $transaction->id,
        );

        $this->finalizePrepaid($transaction, $result);

        return [
            'transaction' => $transaction->fresh(),
            'status' => $transaction->status,
            'message' => $result['message'] ?? null,
            'is_new' => true,
        ];
    }

    private function finalizePrepaid(PpoTransactionModel $tx, array $result): void
    {
        if ($result['status'] === 'success') {
            DB::transaction(function () use ($tx, $result) {
                $tx->status = PpoTransactionStatus::Success->value;
                $tx->provider_status = $result['provider_status'];
                $tx->provider_message = $result['message'];
                $tx->tr_id = $result['tr_id'];
                $tx->sn = $result['sn'];
                $tx->pin = $result['pin'];
                $tx->provider_raw_response = $result['response'];
                $tx->completed_at = now();
                $tx->paid_at = now();
                $tx->save();

                $this->finance->postForSuccess($tx);
            });

            return;
        }

        if ($result['status'] === 'failed') {
            $tx->status = PpoTransactionStatus::Failed->value;
            $tx->provider_status = $result['provider_status'];
            $tx->provider_message = $result['message'];
            $tx->provider_raw_response = $result['response'];
            $tx->save();

            return;
        }

        // processing / pending: await IAK callback.
        $tx->status = PpoTransactionStatus::Processing->value;
        $tx->provider_status = $result['provider_status'];
        $tx->provider_message = $result['message'];
        $tx->tr_id = $result['tr_id'];
        $tx->provider_raw_response = $result['response'];
        $tx->save();
    }

    private function generateReferenceId(): string
    {
        $prefix = (string) config('ppob.reference_prefix', 'PPOB');

        return $prefix.date('ymdHis').strtoupper(Str::random(6));
    }
}
