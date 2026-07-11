<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Mappers\ProductMapper;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    private const DESCENDANT_CACHE_TTL = 1800;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductModel::query()
            ->with($this->relationsForList($filters));

        $this->applyCommonFilters($query, $filters);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->mapPaginatorToEntities($paginator);
    }

    public function findById(int $id): ?Product
    {
        $model = ProductModel::query()
            ->with($this->relationsForDetail())
            ->find($id);

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::query()
            ->with($this->relationsForDetail())
            ->where('slug', $slug)
            ->first();

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findPublishedByStoreId(int $storeId): Collection
    {
        return ProductModel::query()
            ->with($this->relationsForList(['include' => 'summary']))
            ->where('store_id', $storeId)
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductModel $model) => ProductMapper::toEntity($model));
    }

    public function findPublishedByCategorySlug(
        string $categorySlug,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $category = CategoryModel::query()
            ->where('slug', $categorySlug)
            ->orWhere('full_slug', $categorySlug)
            ->first();

        $categoryIds = $category
            ? $this->getCategoryAndDescendantIdsById((int) $category->id)
            : [];

        $query = ProductModel::query()
            ->with($this->relationsForList($filters))
            ->where('status', 'published')
            ->where('is_active', true);

        $this->applyCategoryIdsFilter($query, $categoryIds);
        $this->applyOptionalFilters($query, $filters);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->mapPaginatorToEntities($paginator);
    }

    public function findPublishedByCategoryPath(
        string $path,
        array $filters,
        bool $includeDescendants,
        int $perPage
    ): LengthAwarePaginator {
        $category = CategoryModel::query()->where('full_slug', $path)->first();

        if (! $category) {
            abort(404, 'Category not found.');
        }

        $categoryIds = $includeDescendants
            ? $this->getCategoryAndDescendantIdsById((int) $category->id)
            : [(int) $category->id];

        $query = ProductModel::query()
            ->with($this->relationsForList($filters))
            ->where('status', 'published')
            ->where('is_active', true);

        $this->applyCategoryIdsFilter($query, $categoryIds);
        $this->applyOptionalFilters($query, $filters);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->mapPaginatorToEntities($paginator);
    }

    public function paginateByCategory(
        int $categoryId,
        array $filters,
        bool $includeDescendants
    ): LengthAwarePaginator {
        $categoryIds = $includeDescendants
            ? $this->getCategoryAndDescendantIdsById($categoryId)
            : [$categoryId];

        $query = ProductModel::query()
            ->with($this->relationsForList($filters))
            ->where('status', 'published')
            ->where('is_active', true);

        $this->applyCategoryIdsFilter($query, $categoryIds);
        $this->applyOptionalFilters($query, $filters);

        $perPage = $this->resolvePerPage($filters, 20);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->mapPaginatorToEntities($paginator);
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
        $model->load($this->relationsForDetail());

        return ProductMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return ProductModel::query()->where('id', $id)->delete() > 0;
    }

    private function relationsForList(array $filters = []): array
    {
        $include = (string) ($filters['include'] ?? $filters['view'] ?? 'summary');

        if ($include === 'full') {
            return $this->relationsForDetail();
        }

        return [
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

    private function relationsForDetail(): array
    {
        return [
            'primaryCategory',
            'categories',
            'store',
            'attributeValues.attribute',
            'variants.values.attribute',
            'images',
        ];
    }

    private function applyCommonFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $filters['is_active']);
        }

        if (! empty($filters['category_id'])) {
            $categoryIds = $this->getCategoryAndDescendantIdsById((int) $filters['category_id']);
            $this->applyCategoryIdsFilter($query, $categoryIds);
        }

        $this->applyOptionalFilters($query, $filters);
    }

    private function applyOptionalFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['store_id'])) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        $catalogGroupId = $this->resolveCatalogGroupId($filters);
        if ($catalogGroupId !== null) {
            $this->applyCatalogGroupFilter($query, $catalogGroupId);
        }

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%')
                    ->orWhereHas('variants', function ($query) use ($search) {
                        $query->where('sku', 'like', '%' . $search . '%');
                    });
            });
        }
    }

    private function applyCategoryIdsFilter(Builder $query, array $categoryIds): void
    {
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));

        if (empty($categoryIds)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($query) use ($categoryIds) {
            $query
                ->whereIn('primary_category_id', $categoryIds)
                ->orWhereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
        });
    }

    private function applyCatalogGroupFilter(Builder $query, int $catalogGroupId): void
    {
        $query->where(function ($query) use ($catalogGroupId) {
            $query
                ->whereHas('primaryCategory', function ($query) use ($catalogGroupId) {
                    $query->where('catalog_group_id', $catalogGroupId);
                })
                ->orWhereHas('categories', function ($query) use ($catalogGroupId) {
                    $query->where('categories.catalog_group_id', $catalogGroupId);
                });
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
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function getCategoryAndDescendantIdsById(int $categoryId): array
    {
        return Cache::remember("catalog_category_descendants_{$categoryId}", self::DESCENDANT_CACHE_TTL, function () use ($categoryId) {
            $categoryExists = CategoryModel::query()->where('id', $categoryId)->exists();
            if (! $categoryExists) {
                return [];
            }

            $ids = [$categoryId];
            $parentIds = [$categoryId];

            while (! empty($parentIds)) {
                $childIds = CategoryModel::query()
                    ->whereIn('parent_id', $parentIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $childIds = array_values(array_diff($childIds, $ids));
                if (empty($childIds)) {
                    break;
                }

                $ids = array_merge($ids, $childIds);
                $parentIds = $childIds;
            }

            return array_values(array_unique(array_map('intval', $ids)));
        });
    }

    private function resolvePerPage(array $filters, int $fallback): int
    {
        $raw = $filters['per_page'] ?? $filters['limit'] ?? $fallback;
        $perPage = (int) $raw;

        return max(1, min($perPage, 60));
    }

    private function mapPaginatorToEntities(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->through(
            fn (ProductModel $model) => ProductMapper::toEntity($model)
        );
    }
}
