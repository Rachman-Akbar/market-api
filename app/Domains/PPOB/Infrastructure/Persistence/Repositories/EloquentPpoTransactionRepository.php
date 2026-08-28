<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoTransaction;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPpoTransactionRepository implements PpoTransactionRepositoryInterface
{
    public function findById(int $id): ?PpoTransaction
    {
        $model = PpoTransactionModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByReferenceId(string $referenceId): ?PpoTransaction
    {
        $model = PpoTransactionModel::where('reference_id', $referenceId)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTrId(string $trId): ?PpoTransaction
    {
        $model = PpoTransactionModel::where('tr_id', $trId)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): PpoTransaction
    {
        $model = PpoTransactionModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): PpoTransaction
    {
        $model = PpoTransactionModel::findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function getByUser(string $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PpoTransactionModel::where('user_id', $userId)
            ->with(['operator', 'product'])
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(function ($model) {
            return $this->toArray($model);
        });

        return $paginator;
    }

    private function toArray(PpoTransactionModel $model): array
    {
        return [
            'id' => $model->id,
            'reference_id' => $model->reference_id,
            'product_name' => $model->product_name,
            'category' => $model->category,
            'product_type' => $model->product_type,
            'customer_id' => $model->customer_id,
            'customer_name' => $model->customer_name,
            'total_amount' => (float) $model->total_amount,
            'status' => $model->status,
            'provider_status' => $model->provider_status,
            'provider_message' => $model->provider_message,
            'tr_id' => $model->tr_id,
            'sn' => $model->sn,
            'created_at' => $model->created_at?->toDateTimeString(),
            'completed_at' => $model->completed_at?->toDateTimeString(),
            'operator' => $model->operator ? [
                'id' => $model->operator->id,
                'name' => $model->operator->name,
                'icon_url' => $model->operator->icon_url,
            ] : null,
        ];
    }

    private function toEntity(PpoTransactionModel $model): PpoTransaction
    {
        return new PpoTransaction(
            id: $model->id,
            referenceId: $model->reference_id,
            userId: $model->user_id,
            operatorId: $model->operator_id,
            productId: $model->product_id,
            providerProductCode: $model->provider_product_code,
            productName: $model->product_name,
            category: $model->category,
            productType: $model->product_type,
            customerId: $model->customer_id,
            customerName: $model->customer_name,
            billAmount: $model->bill_amount !== null ? (float) $model->bill_amount : null,
            providerPrice: (float) $model->provider_price,
            adminFee: (float) $model->admin_fee,
            commission: (float) $model->commission,
            margin: (float) $model->margin,
            revenue: (float) $model->revenue,
            netProfit: (float) $model->net_profit,
            totalAmount: (float) $model->total_amount,
            status: $model->status,
            providerStatus: $model->provider_status,
            providerMessage: $model->provider_message,
            trId: $model->tr_id,
            sn: $model->sn,
            pin: $model->pin,
            providerRawResponse: $model->provider_raw_response,
            callbackSignature: $model->callback_signature,
            paidAt: $model->paid_at?->toDateTimeString(),
            completedAt: $model->completed_at?->toDateTimeString(),
            expiresAt: $model->expires_at?->toDateTimeString(),
            cancelledAt: $model->cancelled_at?->toDateTimeString(),
            metadata: $model->metadata,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
