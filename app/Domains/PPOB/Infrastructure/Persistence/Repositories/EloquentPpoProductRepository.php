<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoProduct;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoProductModel;

class EloquentPpoProductRepository implements PpoProductRepositoryInterface
{
    public function findById(int $id): ?PpoProduct
    {
        $model = PpoProductModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByProviderCode(string $providerProductCode): ?PpoProduct
    {
        $model = PpoProductModel::where('provider_product_code', $providerProductCode)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function getAvailableByCategory(string $category, ?int $operatorId = null): array
    {
        $query = PpoProductModel::where('category', $category)
            ->where('status', 'active')
            ->where('is_available', true)
            ->where('is_active', true)
            ->orderBy('selling_price');

        if ($operatorId !== null) {
            $query->where('operator_id', $operatorId);
        }

        return $query->get()->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function getActiveByOperator(int $operatorId): array
    {
        return PpoProductModel::where('operator_id', $operatorId)
            ->where('status', 'active')
            ->where('is_available', true)
            ->where('is_active', true)
            ->orderBy('selling_price')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    public function create(array $data): PpoProduct
    {
        $model = PpoProductModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): PpoProduct
    {
        $model = PpoProductModel::findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        PpoProductModel::findOrFail($id)->delete();
    }

    private function toEntity(PpoProductModel $model): PpoProduct
    {
        return new PpoProduct(
            id: $model->id,
            operatorId: $model->operator_id,
            category: $model->category,
            productType: $model->product_type,
            providerProductCode: $model->provider_product_code,
            name: $model->name,
            brand: $model->brand,
            nominal: $model->nominal,
            providerPrice: (float) $model->provider_price,
            adminFee: (float) $model->admin_fee,
            commission: (float) $model->commission,
            margin: (float) $model->margin,
            sellingPrice: (float) $model->selling_price,
            status: $model->status,
            isAvailable: (bool) $model->is_available,
            isActive: (bool) $model->is_active,
            iconUrl: $model->icon_url,
            metadata: $model->metadata,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
