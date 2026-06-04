<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\ProductMapper;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Contoh pada fungsi paginate() dan pencarian lainnya:
        $query = ProductModel::query()
        ->with(['primaryCategory', 'categories', 'store', 'images', 'variants']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $categoryIds = $this->getCategoryAndDescendantIdsById(
                (int) $filters['category_id']
            );
            $this->applyCategoryIdsFilter($query, $categoryIds);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%');
            });
        }

        $paginator = $this->applyDefaultOrdering($query)->paginate($perPage);

        return $this->mapPaginatorToEntities($paginator);
    }

    public function findById(int $id): ?Product
    {
        $model = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images', 'variants'])
            ->find($id);

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images', 'variants'])
            ->where('slug', $slug)
            ->first();

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findPublishedByStoreId(int $storeId): Collection
    {
        return ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images', 'variants'])
            ->where('store_id', $storeId)
            ->where('status', 'published')
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
            ->where(function ($query) use ($categorySlug) {
                $query
                    ->where('slug', $categorySlug)
                    ->orWhere('full_slug', $categorySlug);
            })
            ->first();

        $categoryIds = $category
            ? $this->getCategoryAndDescendantIdsById((int) $category->id)
            : [];

        $query = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images', 'variants'])
            ->where('status', 'published');

        if (! empty($categoryIds)) {
            $this->applyCategoryIdsFilter($query, $categoryIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        $paginator = $this->applyDefaultOrdering($query)->paginate($perPage);

        return $this->mapPaginatorToEntities($paginator);
    }

    /**
     * Digunakan oleh ListProductsByCategoryPathUseCase.
     * Mencari produk berdasarkan full_slug category (path).
     */
    public function findPublishedByCategoryPath(
        string $path,
        array $filters = [],
        bool $includeDescendants = false,
        int $perPage = 15
    ): LengthAwarePaginator {
        $category = CategoryModel::query()
            ->where('full_slug', $path)
            ->first();

        if (! $category) {
            abort(404, 'Category not found.');
        }

        $categoryIds = $includeDescendants
            ? $this->getCategoryAndDescendantIdsById((int) $category->id)
            : [(int) $category->id];

        $query = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images', 'variants'])
            ->where('status', 'published');

        $this->applyCategoryIdsFilter($query, $categoryIds);

        $perPage = (int) ($filters['per_page'] ?? $perPage);

        $paginator = $this->applyDefaultOrdering($query)->paginate($perPage);

        return $this->mapPaginatorToEntities($paginator);
    }

    /**
     * Digunakan oleh ListProductsByCategoryPathUseCase via paginateByCategory.
     */
    public function paginateByCategory(
        int $categoryId,
        array $filters = [],
        bool $includeDescendants = false
    ): LengthAwarePaginator {
        $categoryIds = $includeDescendants
            ? $this->getCategoryAndDescendantIdsById($categoryId)
            : [$categoryId];

        $query = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images', 'variants'])
            ->where('status', 'published');

        $this->applyCategoryIdsFilter($query, $categoryIds);

        $perPage = (int) ($filters['per_page'] ?? 12);

        $paginator = $this->applyDefaultOrdering($query)->paginate($perPage);

        return $this->mapPaginatorToEntities($paginator);
    }

    public function save(Product $product): Product
    {
        $model = $product->id()
            ? ProductModel::find($product->id())
            : null;

        if (! $model) {
            $model = ProductMapper::toModel($product);
        } else {
            $model->store_id            = $product->storeId();
            $model->primary_category_id = $product->primaryCategoryId();
            $model->seller_id           = $product->sellerId();
            $model->name                = $product->name();
            $model->slug                = $product->slug();
            $model->description         = $product->description();
            $model->thumbnail           = $product->thumbnail();
            $model->status              = $product->status();
        }

        $model->save();

        $categoryIds = $product->categoryIds();

        if (
            $product->primaryCategoryId()
            && ! in_array($product->primaryCategoryId(), $categoryIds, true)
        ) {
            $categoryIds[] = $product->primaryCategoryId();
        }

        $syncPayload = [];
        foreach (array_values(array_unique($categoryIds)) as $categoryId) {
            $syncPayload[$categoryId] = [
                'is_primary' => (int) ($categoryId === $product->primaryCategoryId()),
            ];
        }

        $model->categories()->sync($syncPayload);
        $model->load(['primaryCategory', 'categories', 'store', 'images', 'variants']);

        return ProductMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return ProductModel::where('id', $id)->delete() > 0;
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function applyCategoryIdsFilter($query, array $categoryIds): void
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

    private function applyDefaultOrdering($query)
    {
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function getCategoryAndDescendantIdsById(int $categoryId): array
    {
        $categoryExists = CategoryModel::query()
            ->where('id', $categoryId)
            ->exists();

        if (! $categoryExists) {
            return [];
        }

        $ids       = [$categoryId];
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

            $ids       = array_merge($ids, $childIds);
            $parentIds = $childIds;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function mapPaginatorToEntities(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (ProductModel $model) => ProductMapper::toEntity($model))
        );

        return $paginator;
    }
}
