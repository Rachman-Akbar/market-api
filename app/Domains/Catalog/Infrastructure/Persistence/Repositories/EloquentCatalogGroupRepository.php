<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use Illuminate\Support\Collection;

class EloquentCatalogGroupRepository implements CatalogGroupRepositoryInterface
{
    public function getAll(): Collection
    {
        return CatalogGroupModel::query()
            ->get()
            ->map(fn ($model) => CatalogGroupMapper::toDomain($model));
    }

    public function findById(string $id): ?CatalogGroup
    {
        $model = CatalogGroupModel::find($id);

        if (!$model) {
            return null;
        }

        return CatalogGroupMapper::toDomain($model);
    }

    public function create(CatalogGroup $data): CatalogGroup
    {
        $model = CatalogGroupModel::create([
            'id' => $data->id,
            'name' => $data->name,
        ]);

        return CatalogGroupMapper::toDomain($model);
    }

    public function update(string $id, array $data): CatalogGroup
    {
        $model = CatalogGroupModel::findOrFail($id);
        $model->update($data);

        return CatalogGroupMapper::toDomain($model);
    }

    public function delete(string $id): bool
    {
        return CatalogGroupModel::destroy($id) > 0;
    }
}
