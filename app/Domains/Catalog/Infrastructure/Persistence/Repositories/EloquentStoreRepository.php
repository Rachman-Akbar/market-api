<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Entities\Store;
use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\StoreMapper;

class EloquentStoreRepository implements StoreRepositoryInterface
{
    public function all()
    {
        return StoreModel::all()
            ->map(fn ($model) => StoreMapper::toEntity($model));
    }

    public function findById(string $id): ?Store
    {
        $model = StoreModel::find($id);

        return $model
            ? StoreMapper::toEntity($model)
            : null;
    }

    public function create(Store $store): Store
    {
        $model = StoreModel::create(
            StoreMapper::toModel($store)
        );

        return StoreMapper::toEntity($model);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = StoreModel::query()->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => StoreMapper::toEntity($model))
        );

        return $paginator;
    }
}