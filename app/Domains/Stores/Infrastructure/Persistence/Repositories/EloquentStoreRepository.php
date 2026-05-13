<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\Persistence\Repositories;

use App\Domains\Stores\Domain\Entities\Store as StoreEntity;
use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Stores\Infrastructure\Persistence\Mappers\StoreMapper;
use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentStoreRepository implements StoreRepositoryInterface
{
    private const DEFAULT_PER_PAGE = 8;
    private const MAX_PER_PAGE = 24;

    public function all(): array
    {
        return StoreModel::query()
            ->select($this->storeListColumns())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StoreModel $model): StoreEntity => StoreMapper::toEntity($model))
            ->all();
    }

    public function paginate(
        array $filters = [],
        int $perPage = self::DEFAULT_PER_PAGE
    ): LengthAwarePaginator {
        $perPage = $this->resolvePerPage($filters, $perPage);

        $query = StoreModel::query()
            ->select($this->storeListColumns());

        $this->applyStoreFilters($query, $filters);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator
                ->getCollection()
                ->map(fn (StoreModel $model): StoreEntity => StoreMapper::toEntity($model))
        );

        return $paginator;
    }

    public function listStores(array $filters = []): Collection
    {
        $query = StoreModel::query()
            ->select($this->storeListColumns());

        $this->applyStoreFilters($query, $filters);

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (StoreModel $model): StoreEntity => StoreMapper::toEntity($model));
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
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $model = StoreModel::query()
            ->with('detail')
            ->where('slug', $slug)
            ->first();

        return $model ? StoreMapper::toEntity($model) : null;
    }

    public function create(StoreEntity $store): StoreEntity
    {
        $model = StoreModel::query()->create(StoreMapper::toModel($store));

        $model->load('detail');

        return StoreMapper::toEntity($model);
    }

    public function listProductsByStoreSlug(string $slug): Collection
    {
        $slug = trim($slug);

        if ($slug === '') {
            return collect();
        }

        $storeId = DB::table('stores')
            ->where('slug', $slug)
            ->value('id');

        if (! $storeId) {
            return collect();
        }

        return DB::table('products')
            ->select([
                'id',
                'store_id',
                'primary_category_id',
                'seller_id',
                'name',
                'slug',
                'sku',
                'description',
                'short_description',
                'brand',
                'weight_gram',
                'price',
                'stock',
                'thumbnail',
                'status',
                'is_featured',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function applyStoreFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%');
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '') {
            $query->where('is_active', $this->toBoolean($filters['is_active']));
        }
    }

    private function resolvePerPage(array $filters, int $fallback): int
    {
        $perPage = (int) ($filters['per_page'] ?? $fallback);

        if ($perPage < 1) {
            return self::DEFAULT_PER_PAGE;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function storeListColumns(): array
    {
        return [
            'id',
            'user_id',
            'name',
            'slug',
            'description',
            'logo',
            'is_active',
            'created_at',
            'updated_at',
        ];
    }
}