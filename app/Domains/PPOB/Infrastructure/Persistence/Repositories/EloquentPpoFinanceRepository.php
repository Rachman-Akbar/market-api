<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoFinanceEntry;
use App\Domains\PPOB\Domain\Repositories\PpoFinanceRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoFinanceEntryModel;

class EloquentPpoFinanceRepository implements PpoFinanceRepositoryInterface
{
    /**
     * Insert-or-skip using the unique key (source_type, source_id, transaction_type).
     * Duplicate finance posting is prevented at DB level (idempotent).
     */
    public function createUniquely(array $data): ?PpoFinanceEntry
    {
        try {
            $model = PpoFinanceEntryModel::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Duplicate unique key -> already posted once
            return null;
        }

        return $this->toEntity($model);
    }

    public function getByReferenceId(string $referenceId): array
    {
        return PpoFinanceEntryModel::where('reference_id', $referenceId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    private function toEntity(PpoFinanceEntryModel $model): PpoFinanceEntry
    {
        return new PpoFinanceEntry(
            id: $model->id,
            sourceType: $model->source_type,
            sourceId: (string) $model->source_id,
            ppobTransactionId: $model->ppob_transaction_id,
            referenceId: $model->reference_id,
            transactionType: $model->transaction_type,
            title: $model->title,
            description: $model->description,
            amount: (float) $model->amount,
            status: $model->status,
            occurredAt: $model->occurred_at?->toDateTimeString(),
            metadata: $model->metadata,
            isActive: (bool) $model->is_active,
        );
    }
}
