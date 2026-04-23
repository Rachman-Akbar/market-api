<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use Illuminate\Support\Collection;

final class EloquentCatalogGroupRepository implements CatalogGroupRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = CatalogGroupModel::query()
            ->with(['categories' => fn ($q) => $q->withCount('products')]);

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->get()
            ->map(fn ($model) => CatalogGroupMapper::toEntity($model));
    }

    public function findById(int $id): ?CatalogGroup
    {
        $model = CatalogGroupModel::with(['categories' => fn ($q) => $q->withCount('products')])
            ->find($id);

        return $model ? CatalogGroupMapper::toEntity($model) : null;
    }

    public function create(CatalogGroup $data): CatalogGroup
    {
        $model = CatalogGroupMapper::toModel($data);
        $model->save();
        $model->load('categories');

        return CatalogGroupMapper::toEntity($model);
    }

    public function update(int $id, array $data): CatalogGroup
    {
        $model = CatalogGroupModel::findOrFail($id);
        $model->update($data);
        $model->load('categories');

        return CatalogGroupMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return CatalogGroupModel::destroy($id) > 0;
    }
}