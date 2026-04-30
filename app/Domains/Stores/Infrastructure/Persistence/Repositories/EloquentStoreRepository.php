<?php

namespace App\Domains\Stores\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Stores\Domain\Entities\Store as StoreEntity;
use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Stores\Infrastructure\Persistence\Mappers\StoreMapper;
use App\Models\Product;

final class EloquentStoreRepository implements StoreRepositoryInterface
{
    public function all(): array
    {
        return StoreModel::query()
            ->with('detail')
            ->latest()
            ->get()
            ->map(fn ($model) => StoreMapper::toEntity($model))
            ->all();
    }

    public function paginate(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = StoreModel::query()->with('detail');

        if (! empty($filters['search'])) {
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

    public function listStores(array $filters = []): Collection
    {
        $query = StoreModel::query()->with('detail');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query
            ->latest()
            ->get()
            ->map(fn ($model) => StoreMapper::toEntity($model));
    }

    public function findById(int $id): ?StoreEntity
    {
        $model = StoreModel::query()
            ->with('detail')
            ->find($id);

        return $model ? StoreMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?StoreEntity
    {
        $model = StoreModel::query()
            ->with('detail')
            ->where('slug', $slug)
            ->first();

        return $model ? StoreMapper::toEntity($model) : null;
    }

    public function create(StoreEntity $store): StoreEntity
    {
        $model = StoreModel::create(StoreMapper::toModel($store));
        $model->load('detail');

        return StoreMapper::toEntity($model);
    }

    public function listProductsByStoreSlug(string $slug): Collection
    {
        $store = StoreModel::query()
            ->where('slug', $slug)
            ->first();

        if (! $store) {
            return collect();
        }

        return Product::query()
            ->where('store_id', $store->id)
            ->latest()
            ->get();
    }
}
