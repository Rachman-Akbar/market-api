<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Mappers\ProductMapper;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductVariantModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = $this->buildListQuery($filters);
        $this->applySorting($query, $filters);

        return $query
            ->get()
            ->map(fn (ProductModel $model): Product => ProductMapper::toEntity($model));
    }

    public function cursorPaginate(array $filters = [], int $perPage = 24): CursorPaginator
    {
        $query = $this->buildListQuery($filters);
        $this->applySorting($query, $filters);

        $paginator = $query
            ->cursorPaginate(
                max(1, min(50, $perPage)),
                ['*'],
                'cursor'
            );

        return $paginator;
    }

    public function paginate(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = $this->buildListQuery($filters);
        $this->applySorting($query, $filters);

        $paginator = $query
            ->paginate(
                max(1, min(100, $perPage)),
                ['*'],
                'page',
                max(1, $page)
            );

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (ProductModel $model): Product => ProductMapper::toEntity($model))
        );

        return $paginator;
    }

    public function findById(int $id, bool $includeInactive = false): ?Product
    {
        $model = ProductModel::query()
            ->when(! $includeInactive, function (Builder $query): void {
                $query->active()->where('products.status', 'published');
                $this->applyPublicStoreFilter($query);
            })
            ->with($this->relationsForDetail($includeInactive))
            ->find($id);

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug, bool $includeInactive = false): ?Product
    {
        $model = ProductModel::query()
            ->when(! $includeInactive, function (Builder $query): void {
                $query->active()->where('products.status', 'published');
                $this->applyPublicStoreFilter($query);
            })
            ->with($this->relationsForDetail($includeInactive))
            ->where('slug', $slug)
            ->first();

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function nameExistsForStore(string $name, int $storeId, ?int $ignoreId = null): bool
    {
        return ProductModel::withTrashed()
            ->where('store_id', $storeId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower(trim($name))])
            ->when($ignoreId !== null, fn (Builder $query) => $query->where($query->getModel()->getQualifiedKeyName(), '!=', $ignoreId))
            ->exists();
    }

    public function findPublishedByStoreId(int $storeId): Collection
    {
        return ProductModel::query()
            ->active()
            ->whereHas('store', fn (Builder $query) => $query->publiclyAvailable())
            ->with($this->relationsForList(['include' => 'summary']))
            ->where('store_id', $storeId)
            ->where('products.status', 'published')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductModel $model): Product => ProductMapper::toEntity($model));
    }

    public function findPublishedByCategorySlug(
        string $categorySlug,
        array $filters = []
    ): Collection {
        $category = CategoryModel::query()
            ->active()
            ->whereHas('catalogGroup', fn (Builder $query) => $query->active())
            ->where(function (Builder $query) use ($categorySlug): void {
                $query->where('slug', $categorySlug)
                    ->orWhere('full_slug', $categorySlug);
            })
            ->first();

        $categoryIds = $category
            ? $this->getCategoryAndDescendantIdsById((int) $category->id)
            : [];

        $query = ProductModel::query()
            ->active()
            ->whereHas('store', fn (Builder $query) => $query->publiclyAvailable())
            ->with($this->relationsForList($filters))
            ->where('products.status', 'published');

        $this->applyCategoryIdsFilter($query, $categoryIds);
        $this->applyOptionalFilters($query, $filters);

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductModel $model): Product => ProductMapper::toEntity($model));
    }

    public function findPublishedByCategoryPath(
        string $path,
        array $filters,
        bool $includeDescendants
    ): Collection {
        $category = CategoryModel::query()
            ->active()
            ->whereHas('catalogGroup', fn (Builder $query) => $query->active())
            ->where('full_slug', $path)
            ->first();

        if (! $category) {
            abort(404, 'Category not found.');
        }

        $categoryIds = $includeDescendants
            ? $this->getCategoryAndDescendantIdsById((int) $category->id)
            : [(int) $category->id];

        $query = ProductModel::query()
            ->active()
            ->whereHas('store', fn (Builder $query) => $query->publiclyAvailable())
            ->with($this->relationsForList($filters))
            ->where('products.status', 'published');

        $this->applyCategoryIdsFilter($query, $categoryIds);
        $this->applyOptionalFilters($query, $filters);

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductModel $model): Product => ProductMapper::toEntity($model));
    }

    public function findByCategory(
        int $categoryId,
        array $filters,
        bool $includeDescendants
    ): Collection {
        $categoryIds = $includeDescendants
            ? $this->getCategoryAndDescendantIdsById($categoryId)
            : [$categoryId];

        $query = ProductModel::query()
            ->active()
            ->whereHas('store', fn (Builder $query) => $query->publiclyAvailable())
            ->with($this->relationsForList($filters))
            ->where('products.status', 'published');

        $this->applyCategoryIdsFilter($query, $categoryIds);
        $this->applyOptionalFilters($query, $filters);

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductModel $model): Product => ProductMapper::toEntity($model));
    }

    public function save(Product $product): Product
    {
        $model = $product->id()
            ? ProductModel::query()->find($product->id())
            : null;

        if (! $model) {
            $model = ProductMapper::toModel($product);
        } else {
            $model->store_id = $product->storeId();
            $model->primary_category_id = $product->primaryCategoryId();
            $model->name = $product->name();
            $model->slug = $product->slug();
            $model->description = $product->description();
            $model->brand = $product->brand();
            $model->thumbnail = $product->thumbnail();
            $model->status = $product->status();
            $model->is_active = $product->isActive();
        }

        $model->save();

        $categoryIds = $product->categoryIds();

        if ($product->primaryCategoryId() && ! in_array($product->primaryCategoryId(), $categoryIds, true)) {
            $categoryIds[] = $product->primaryCategoryId();
        }

        $syncPayload = [];

        foreach (array_values(array_unique($categoryIds)) as $categoryId) {
            $syncPayload[$categoryId] = [
                'is_primary' => (int) ($categoryId === $product->primaryCategoryId()),
            ];
        }

        $model->categories()->sync($syncPayload);
        $model->load($this->relationsForDetail(true));

        return ProductMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = ProductModel::query()->find($id);

        return $model ? (bool) $model->delete() : false;
    }

    private function buildListQuery(array $filters): Builder
    {
        $includeInactive = (bool) ($filters['include_inactive'] ?? false);
        $query = ProductModel::query()
            ->with($this->relationsForList($filters, $includeInactive));

        if (! $includeInactive) {
            $this->applyPublicStoreFilter($query);
        }

        $this->applyCommonFilters($query, $filters, $includeInactive);

        return $query;
    }

    private function relationsForList(array $filters = [], bool $includeInactive = false): array
    {
        $include = (string) ($filters['include'] ?? $filters['view'] ?? 'summary');

        if ($include === 'full') {
            return $this->relationsForDetail($includeInactive);
        }

        return [
            'store:id,name,slug,logo,city,province,status,is_active',
            'images' => fn ($query) => $query
                ->select(['id', 'product_id', 'url', 'alt_text', 'is_primary', 'sort_order', 'created_at', 'updated_at'])
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id'),
            'variants' => fn ($query) => $query
                ->select(['id', 'product_id', 'store_id', 'sku', 'name', 'price', 'stock', 'is_default', 'created_at', 'updated_at'])
                ->orderByDesc('is_default')
                ->orderBy('id'),
        ];
    }

    private function relationsForDetail(bool $includeInactive = false): array
    {
        return [
            'primaryCategory' => fn ($query) => $query->when(! $includeInactive, fn ($query) => $query
                ->active()
                ->whereHas('catalogGroup', fn ($groupQuery) => $groupQuery->active())),
            'categories' => fn ($query) => $query->when(! $includeInactive, fn ($query) => $query
                ->active()
                ->whereHas('catalogGroup', fn ($groupQuery) => $groupQuery->active())),
            'store',
            'attributeValues.attribute',
            'variants.values.attribute',
            'images',
        ];
    }

    private function applyCommonFilters(Builder $query, array $filters, bool $includeInactive): void
    {
        if (! empty($filters['status'])) {
            $status = Str::lower(trim((string) $filters['status']));
            $query->where('products.status', $status);
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where(
                'is_active',
                filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                    ?? (bool) $filters['is_active']
            );
        } elseif (! $includeInactive) {
            $query->active();
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $includeDescendants = filter_var(
                $filters['include_descendants'] ?? true,
                FILTER_VALIDATE_BOOLEAN
            );
            $categoryIds = $includeDescendants
                ? $this->getCategoryAndDescendantIdsById($categoryId)
                : [$categoryId];

            $this->applyCategoryIdsFilter($query, $categoryIds);
        } else {
            $categorySlug = trim((string) ($filters['category_slug'] ?? $filters['category'] ?? ''));

            if ($categorySlug !== '') {
                $category = CategoryModel::query()
                    ->active()
                    ->whereHas('catalogGroup', fn (Builder $categoryQuery) => $categoryQuery->active())
                    ->where(function (Builder $categoryQuery) use ($categorySlug): void {
                        $categoryQuery
                            ->where('slug', $categorySlug)
                            ->orWhere('full_slug', $categorySlug);
                    })
                    ->first();

                $categoryIds = $category
                    ? $this->getCategoryAndDescendantIdsById((int) $category->id)
                    : [];

                $this->applyCategoryIdsFilter($query, $categoryIds);
            }
        }

        $this->applyOptionalFilters($query, $filters);
    }

    private function applyOptionalFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['store_id'])) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        $name = trim((string) ($filters['name'] ?? ''));

        if ($name !== '') {
            $query->where('products.name', 'like', '%' . $name . '%');
        }

        $mode = Str::lower(trim((string) ($filters['mode'] ?? '')));

        if ($mode === 'variant') {
            $query->has('variants', '>', 1);
        } elseif ($mode === 'simple') {
            $query->has('variants', '<=', 1);
        }

        $this->applyVariantRangeFilter($query, 'price', $filters['price_min'] ?? null, $filters['price_max'] ?? null);
        $this->applyVariantRangeFilter($query, 'stock', $filters['stock_min'] ?? null, $filters['stock_max'] ?? null);

        if (! empty($filters['seller_id'])) {
            $sellerId = (string) $filters['seller_id'];
            $query->whereHas('store', fn (Builder $storeQuery) => $storeQuery->where('user_id', $sellerId));
        }

        $catalogGroupId = $this->resolveCatalogGroupId($filters);

        if ($catalogGroupId !== null) {
            $this->applyCatalogGroupFilter($query, $catalogGroupId);
        }

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%')
                    ->orWhereHas('variants', fn (Builder $query) => $query->where('sku', 'like', '%' . $search . '%'));
            });
        }
    }

    private function applyVariantRangeFilter(
        Builder $query,
        string $column,
        mixed $minimum,
        mixed $maximum
    ): void {
        if (! in_array($column, ['price', 'stock'], true)) {
            return;
        }

        $subquery = fn () => ProductVariantModel::query()
            ->select($column)
            ->whereColumn('product_id', 'products.id')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->limit(1);

        if ($minimum !== null && $minimum !== '' && is_numeric($minimum)) {
            $query->where($subquery(), '>=', (float) $minimum);
        }

        if ($maximum !== null && $maximum !== '' && is_numeric($maximum)) {
            $query->where($subquery(), '<=', (float) $maximum);
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = Str::lower(trim((string) ($filters['sort_by'] ?? 'created_at')));
        $direction = Str::lower(trim((string) ($filters['sort_direction'] ?? 'desc'))) === 'asc'
            ? 'asc'
            : 'desc';

        if ($sortBy === 'store_name') {
            $query->orderBy(
                DB::table('stores')
                    ->select('name')
                    ->whereColumn('stores.id', 'products.store_id')
                    ->limit(1),
                $direction
            );
        } elseif ($sortBy === 'price' || $sortBy === 'stock') {
            $query->orderBy(
                ProductVariantModel::query()
                    ->select($sortBy)
                    ->whereColumn('product_id', 'products.id')
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->limit(1),
                $direction
            );
        } elseif ($sortBy === 'mode') {
            $query->withCount('variants')->orderBy('variants_count', $direction);
        } else {
            $allowed = ['id', 'name', 'status', 'is_active', 'created_at', 'updated_at'];
            $column = in_array($sortBy, $allowed, true) ? $sortBy : 'created_at';
            $query->orderBy('products.' . $column, $direction);
        }

        if ($sortBy !== 'id') {
            $query->orderBy('products.id', $direction);
        }
    }

    private function applyCategoryIdsFilter(Builder $query, array $categoryIds): void
    {
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));

        if ($categoryIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $query) use ($categoryIds): void {
            $query
                ->whereIn('primary_category_id', $categoryIds)
                ->orWhereHas('categories', fn (Builder $query) => $query->whereIn('categories.id', $categoryIds));
        });
    }

    private function applyCatalogGroupFilter(Builder $query, int $catalogGroupId): void
    {
        $query->where(function (Builder $query) use ($catalogGroupId): void {
            $query
                ->whereHas('primaryCategory', fn (Builder $query) => $query->where('catalog_group_id', $catalogGroupId))
                ->orWhereHas('categories', fn (Builder $query) => $query->where('categories.catalog_group_id', $catalogGroupId));
        });
    }

    private function resolveCatalogGroupId(array $filters): ?int
    {
        if (! empty($filters['catalog_group_id'])) {
            return (int) $filters['catalog_group_id'];
        }

        $slug = trim((string) ($filters['catalog_group_slug'] ?? ''));

        if ($slug === '') {
            return null;
        }

        $id = DB::table('catalog_groups')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function getCategoryAndDescendantIdsById(int $categoryId): array
    {
        $categoryExists = CategoryModel::query()
            ->active()
            ->whereHas('catalogGroup', fn (Builder $query) => $query->active())
            ->whereKey($categoryId)
            ->exists();

        if (! $categoryExists) {
            return [];
        }

        $ids = [$categoryId];
        $parentIds = [$categoryId];

        while ($parentIds !== []) {
            $childIds = CategoryModel::query()
                ->active()
                ->whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $childIds = array_values(array_diff($childIds, $ids));

            if ($childIds === []) {
                break;
            }

            $ids = array_merge($ids, $childIds);
            $parentIds = $childIds;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function applyPublicStoreFilter(Builder $query): void
    {
        $query->whereHas('store', fn (Builder $storeQuery) => $storeQuery->publiclyAvailable());
    }

}
