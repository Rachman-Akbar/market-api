<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\UseCases;

use App\Domains\PPOB\Application\Services\IakProviderService;
use App\Domains\PPOB\Application\Services\InvoiceService;
use App\Domains\PPOB\Application\Services\PpoFinanceService;
use App\Domains\PPOB\Domain\Entities\PpoTransactionStatus;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Submits a paid PPOB top-up to the provider and advances it through the
 * provider lifecycle (success / failed / processing). Idempotent: it is a
 * no-op once the transaction has already left the pending/payment state.
 */
final class FinalizePpobTopUpUseCase
{
    public function __construct(
        private PpoTransactionRepositoryInterface $transactions,
        private IakProviderService $provider,
        private PpoFinanceService $finance,
        private InvoiceService $invoices,
    ) {}

    public function executeById(int $id): void
    {
        $tx = PpoTransactionModel::findOrFail($id);

        $this->executeFor($tx);
    }

    public function executeFor(PpoTransactionModel $tx): void
    {
        // Do not re-submit terminal or already-in-flight top-ups.
        if (in_array($tx->status, [
            PpoTransactionStatus::Success->value,
            PpoTransactionStatus::Processing->value,
        ], true)) {
            return;
        }

        // Only a paid/confirmed transaction may be submitted.
        if ($tx->payment_status !== 'paid') {
            throw new RuntimeException('Transaksi belum dibayar, top-up tidak dapat diproses.', 422);
        }

        // Submit to IAK outside the DB transaction (network I/O).
        $result = $this->provider->submitTopUp(
            $tx->reference_id,
            $tx->customer_id,
            $tx->provider_product_code,
            $tx->id,
        );

        if ($result['status'] === 'success') {
            DB::transaction(function () use ($tx, $result): void {
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

                // Generate a default invoice for successful digital top-ups.
                $this->invoices->generateForTransaction($tx->fresh());

                // Queue the invoice email (idempotent, one send per invoice).
                $this->invoices->sendForTransaction($tx->fresh());
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

    /**
     * Mark a paid transaction and submit its top-up. Used by payment webhooks.
     */
    public function markPaidAndSubmit(int $transactionId, string $midtransTransactionId): void
    {
        $tx = PpoTransactionModel::findOrFail($transactionId);

        $tx->payment_status = 'paid';
        $tx->midtrans_transaction_id = $midtransTransactionId;
        $tx->save();

        $this->executeFor($tx);
    }
}
