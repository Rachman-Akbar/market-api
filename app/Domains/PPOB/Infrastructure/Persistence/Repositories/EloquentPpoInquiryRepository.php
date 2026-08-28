<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoInquiry;
use App\Domains\PPOB\Domain\Repositories\PpoInquiryRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoInquiryModel;

class EloquentPpoInquiryRepository implements PpoInquiryRepositoryInterface
{
    public function findById(int $id): ?PpoInquiry
    {
        $model = PpoInquiryModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findActiveByReferenceId(string $referenceId): ?PpoInquiry
    {
        $model = PpoInquiryModel::where('reference_id', $referenceId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): PpoInquiry
    {
        $model = PpoInquiryModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): PpoInquiry
    {
        $model = PpoInquiryModel::findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    private function toEntity(PpoInquiryModel $model): PpoInquiry
    {
        return new PpoInquiry(
            id: $model->id,
            referenceId: $model->reference_id,
            userId: $model->user_id,
            operatorId: $model->operator_id,
            productCode: $model->product_code,
            category: $model->category,
            customerId: $model->customer_id,
            trId: $model->tr_id,
            customerName: $model->customer_name,
            customerNo: $model->customer_no,
            billAmount: $model->bill_amount !== null ? (float) $model->bill_amount : null,
            adminCharge: $model->admin_charge !== null ? (float) $model->admin_charge : null,
            adminChargeMessage: $model->admin_charge_message,
            detail: $model->detail,
            status: $model->status,
            expiresAt: $model->expires_at?->toDateTimeString(),
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
