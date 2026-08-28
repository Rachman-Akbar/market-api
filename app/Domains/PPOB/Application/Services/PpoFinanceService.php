<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\PPOB\Domain\Entities\PpoTransaction;
use App\Domains\PPOB\Domain\Repositories\PpoFinanceRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;

/**
 * PPOB finance ledger. Each successful transaction posts balanced, idempotent
 * journal entries (source_type = ppob_transaction, source_id = transaction id).
 * PostgreSQL-style unique constraint prevents double posting.
 */
class PpoFinanceService
{
    public function __construct(
        private PpoFinanceRepositoryInterface $finance,
    ) {}

    /**
     * Post the full ledger for a completed (success) PPOB transaction.
     * Posts are idempotent: only the first call creates the entries.
     */
    public function postForSuccess(PpoTransactionModel $tx): array
    {
        $sourceId = (string) $tx->id;
        $referenceId = $tx->reference_id;
        $occurred = $tx->completed_at ?? now();

        $entries = [
            // Revenue = what the customer paid
            [
                'source_type' => 'ppob_transaction',
                'source_id' => $sourceId,
                'ppob_transaction_id' => $tx->id,
                'reference_id' => $referenceId,
                'transaction_type' => 'revenue',
                'title' => 'PPOB Pendapatan',
                'description' => "Pendapatan transaksi {$referenceId} ({$tx->product_name})",
                'amount' => (float) $tx->revenue,
                'status' => 'posted',
                'occurred_at' => $occurred,
            ],
            // Provider cost
            [
                'source_type' => 'ppob_transaction',
                'source_id' => $sourceId,
                'ppob_transaction_id' => $tx->id,
                'reference_id' => $referenceId,
                'transaction_type' => 'provider_cost',
                'title' => 'HPP Provider IAK',
                'description' => "Biaya provider transaksi {$referenceId}",
                'amount' => (float) $tx->provider_price,
                'status' => 'posted',
                'occurred_at' => $occurred,
            ],
            // Admin fee received
            [
                'source_type' => 'ppob_transaction',
                'source_id' => $sourceId,
                'ppob_transaction_id' => $tx->id,
                'reference_id' => $referenceId,
                'transaction_type' => 'admin_fee',
                'title' => 'Biaya Administrasi',
                'description' => "Biaya admin transaksi {$referenceId}",
                'amount' => (float) $tx->admin_fee,
                'status' => 'posted',
                'occurred_at' => $occurred,
            ],
            // Commission (reseller/agent share)
            [
                'source_type' => 'ppob_transaction',
                'source_id' => $sourceId,
                'ppob_transaction_id' => $tx->id,
                'reference_id' => $referenceId,
                'transaction_type' => 'commission',
                'title' => 'Komisi Transaksi',
                'description' => "Komisi transaksi {$referenceId}",
                'amount' => (float) $tx->commission,
                'status' => 'posted',
                'occurred_at' => $occurred,
            ],
            // Net profit to platform
            [
                'source_type' => 'ppob_transaction',
                'source_id' => $sourceId,
                'ppob_transaction_id' => $tx->id,
                'reference_id' => $referenceId,
                'transaction_type' => 'net_profit',
                'title' => 'Laba Bersih PPOB',
                'description' => "Laba bersih transaksi {$referenceId}",
                'amount' => (float) $tx->net_profit,
                'status' => 'posted',
                'occurred_at' => $occurred,
            ],
        ];

        $created = [];
        foreach ($entries as $entry) {
            $result = $this->finance->createUniquely($entry);
            if ($result !== null) {
                $created[] = $result;
            }
        }

        return $created;
    }

    public function entriesForReference(string $referenceId): array
    {
        return $this->finance->getByReferenceId($referenceId);
    }
}
