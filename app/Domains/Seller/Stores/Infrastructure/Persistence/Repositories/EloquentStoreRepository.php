<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Infrastructure\Persistence\Repositories;

use App\Domains\Seller\Stores\Domain\Entities\Store as StoreEntity;
use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Mappers\StoreMapper;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
        // Mencari data ke DB menggunakan Eloquent Model beserta relasi detailnya
        $model = StoreModel::query()
            ->with('detail')
            ->find($id);

        // Jika model ditemukan, ubah menjadi Domain Entity menggunakan Mapper. Jika tidak, return null.
        return $model ? StoreMapper::toEntity($model) : null;
    }

public function findBySlug(string $slug): ?StoreEntity
{
    // Gunakan trim untuk menghindari spasi tak kasat mata dari URL
    $model = StoreModel::query()
        ->where('slug', trim($slug))
        ->first();

    return $model ? StoreMapper::toEntity($model) : null;
}

    public function create(StoreEntity $store): StoreEntity
    {
        $model = StoreModel::query()->create(StoreMapper::toModel($store));

        $model->load('detail');

        return StoreMapper::toEntity($model);
    }

    public function update(StoreEntity $store): StoreEntity
    {
        // Pastikan entity store memiliki method id() atau getId() sesuai rancanganmu
        $model = StoreModel::query()->findOrFail($store->id());

        $model->update([
            'name'        => $store->name(),
            'slug'        => $store->slug(),
            'description' => $store->description(),
            'logo'        => $store->logo(),
            'is_active'   => $store->isActive() ? 1 : 0,
        ]);

        return StoreMapper::toEntity($model);
    }

    /**
     * Mengambil produk berdasarkan Slug Toko (Berpaginasi & Menggunakan Left Join Variant)
     */
    public function listProductsByStoreSlug(string $slug, array $filters = []): LengthAwarePaginator
    {
        $slug = trim($slug);
        $perPage = (int) ($filters['per_page'] ?? 12);

        // Cari store_id terlebih dahulu lewat slug
        $storeId = DB::table('stores')
            ->where('slug', $slug)
            ->value('id');

        // Jika toko tidak ditemukan, kembalikan paginator kosong bawaan Laravel DB
        if (! $storeId) {
            return DB::table('products')->where('id', 0)->paginate($perPage);
        }

        return DB::table('products')
            // JOIN ke product_variants untuk menarik data SKU, Price, dan Stock dari varian default (is_default = 1)
            ->leftJoin('product_variants', function ($join) {
                $join->on('product_variants.product_id', '=', 'products.id')
                     ->where('product_variants.is_default', '=', 1);
            })
            ->where('products.store_id', $storeId)
            ->where('products.is_active', 1)
            ->where('products.status', 'published')
            ->select([
                'products.id',
                'products.store_id',
                'products.primary_category_id',
                'products.seller_id',
                'products.name',
                'products.slug',
                'products.description',
                'products.brand',
                'products.thumbnail',
                'products.status',
                'products.created_at',
                'products.updated_at',

                // Data Varian dialiaskan agar seolah-olah milik tabel products demi kecocokan resource DTO lama
                'product_variants.sku as sku',
                'product_variants.price as price',
                'product_variants.stock as stock',
            ])
            ->orderByDesc('products.created_at')
            ->orderByDesc('products.id')
            ->paginate($perPage);
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
