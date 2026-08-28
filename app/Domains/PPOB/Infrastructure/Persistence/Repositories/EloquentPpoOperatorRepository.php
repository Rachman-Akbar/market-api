<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoOperator;
use App\Domains\PPOB\Domain\Repositories\PpoOperatorRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoOperatorModel;

class EloquentPpoOperatorRepository implements PpoOperatorRepositoryInterface
{
    public function findById(int $id): ?PpoOperator
    {
        $model = PpoOperatorModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function getActiveByCategory(?string $category = null): array
    {
        $query = PpoOperatorModel::where('is_active', true);

        if ($category !== null) {
            $query->where('category', $category);
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    public function create(array $data): PpoOperator
    {
        $model = PpoOperatorModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): PpoOperator
    {
        $model = PpoOperatorModel::findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        PpoOperatorModel::findOrFail($id)->delete();
    }

    private function toEntity(PpoOperatorModel $model): PpoOperator
    {
        return new PpoOperator(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            category: $model->category,
            brand: $model->brand,
            operatorPrefix: $model->operator_prefix,
            providerName: $model->provider_name,
            iconUrl: $model->icon_url,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
