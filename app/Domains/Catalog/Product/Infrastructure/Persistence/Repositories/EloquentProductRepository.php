<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Mappers\ProductMapper;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductModel::query()
            ->with($this->relations());

        if (! empty($filters['seller_id'])) {
            $query->where('seller_id', (string) $filters['seller_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (! empty($filters['category_id'])) {
            $categoryIds = $this->getCategoryAndDescendantIdsById((int) $filters['category_id']);
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
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return $this->mapPaginatorToEntities($paginator);
    }

    public function findById(int $id): ?Product
    {
        // Memuat semua relasi penting (variants, images, attributeValues) agar data tidak kosong
        $model = ProductModel::with($this->relations())->find($id);
        
        if (! $model) {
            return null;
        }
        
        // FIX: Menggunakan ProductMapper untuk standarisasi konversi ke Domain Entity
        return ProductMapper::toEntity($model);
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::query()
            ->with($this->relations())
            ->where('slug', $slug)
            ->first();

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findPublishedByStoreId(int $storeId): Collection
    {
        return ProductModel::query()
            ->with($this->relations())
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
            ->with($this->relations())
            ->where('status', 'published')
            ->where('is_active', true);

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
                    ->orWhere('brand', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return $this->mapPaginatorToEntities($paginator);
    }

    public function findPublishedByCategoryPath(
        string $path,
        array $filters,
        bool $includeDescendants,
        int $perPage
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
            ->with($this->relations())
            ->where('status', 'published')
            ->where('is_active', true);

        $this->applyCategoryIdsFilter($query, $categoryIds);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

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
            ->with($this->relations())
            ->where('status', 'published')
            ->where('is_active', true);

        $this->applyCategoryIdsFilter($query, $categoryIds);

        $perPage = (int) ($filters['per_page'] ?? 12);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

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
            $model->seller_id = $product->sellerId();
            $model->name = $product->name();
            $model->slug = $product->slug();
            $model->description = $product->description();
            $model->brand = $product->brand();
            $model->thumbnail = $product->thumbnail();
            $model->status = $product->status();
            $model->is_active = $product->isActive();

            if (method_exists($product, 'sku')) {
                $model->sku = $product->sku();
            }
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
        $model->load($this->relations());

        return ProductMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return ProductModel::query()->where('id', $id)->delete() > 0;
    }

    private function relations(): array
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

    private function getCategoryAndDescendantIdsById(int $categoryId): array
    {
        $categoryExists = CategoryModel::query()
            ->where('id', $categoryId)
            ->exists();

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
    }

    private function mapPaginatorToEntities(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->through(
            fn (ProductModel $model) => ProductMapper::toEntity($model)
        );
    }
}