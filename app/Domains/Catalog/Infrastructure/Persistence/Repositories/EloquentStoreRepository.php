<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Entities\Store;
use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\StoreMapper;

final class EloquentStoreRepository implements StoreRepositoryInterface
{
    public function all(): array
    {
        return StoreModel::with('detail')
            ->get()
            ->map(fn ($model) => StoreMapper::toEntity($model))
            ->all();
    }

    public function findById(int $id): ?Store
    {
        $model = StoreModel::with('detail')->find($id);

        return $model ? StoreMapper::toEntity($model) : null;
    }

    public function create(Store $store): Store
    {
        $model = StoreModel::create(StoreMapper::toModel($store));
        $model->load('detail');

        return StoreMapper::toEntity($model);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StoreModel::query()->with('detail');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $paginator = $query->latest()->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => StoreMapper::toEntity($model))
        );

        return $paginator;
    }
}