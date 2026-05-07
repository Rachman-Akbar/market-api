<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

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
        $query = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $categoryIds = $this->getCategoryAndDescendantIdsById(
                (int) $filters['category_id']
            );

            if (! empty($categoryIds)) {
                $query->where(function ($query) use ($categoryIds) {
                    $query
                        ->whereIn('primary_category_id', $categoryIds)
                        ->orWhereHas('categories', function ($query) use ($categoryIds) {
                            $query->whereIn('categories.id', $categoryIds);
                        });
                });
            }
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $paginator = $query
            ->latest()
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => ProductMapper::toEntity($model))
        );

        return $paginator;
    }

    public function findById(int $id): ?Product
    {
        $model = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images'])
            ->find($id);

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images'])
            ->where('slug', $slug)
            ->first();

        return $model ? ProductMapper::toEntity($model) : null;
    }

    public function findPublishedByStoreId(int $storeId): Collection
    {
        return ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images'])
            ->where('store_id', $storeId)
            ->where('status', 'published')
            ->latest()
            ->get()
            ->map(fn ($model) => ProductMapper::toEntity($model));
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
            ->with(['primaryCategory', 'categories', 'store', 'images'])
            ->where('status', 'published');

        if (! empty($categoryIds)) {
            $query->where(function ($query) use ($categoryIds) {
                $query
                    ->whereIn('primary_category_id', $categoryIds)
                    ->orWhereHas('categories', function ($query) use ($categoryIds) {
                        $query->whereIn('categories.id', $categoryIds);
                    });
            });
        } else {
            /**
             * Kalau kategori tidak ditemukan, jangan tampilkan semua produk.
             */
            $query->whereRaw('1 = 0');
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        return $query
            ->latest()
            ->paginate($perPage);
    }

    public function save(Product $product): Product
    {
        $model = $product->id()
            ? ProductModel::find($product->id())
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
            $model->price = $product->price();
            $model->stock = $product->stock();
            $model->thumbnail = $product->thumbnail();
            $model->status = $product->status();
        }

        $model->save();

        $categoryIds = $product->categoryIds();

        if (
            $product->primaryCategoryId()
            && ! in_array($product->primaryCategoryId(), $categoryIds, true)
        ) {
            $categoryIds[] = $product->primaryCategoryId();
        }

        $model->categories()->sync(array_values(array_unique($categoryIds)));

        $model->load(['primaryCategory', 'categories', 'store', 'images']);

        return ProductMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return ProductModel::where('id', $id)->delete() > 0;
    }

    /**
     * Ambil ID kategori itu sendiri + semua child di bawahnya.
     *
     * Level 1:
     * - ambil level 1
     * - ambil semua level 2
     * - ambil semua level 3
     *
     * Level 2:
     * - ambil level 2
     * - ambil semua level 3
     *
     * Level 3:
     * - ambil level 3 saja
     */
    private function getCategoryAndDescendantIdsById(int $categoryId): array
    {
        $category = CategoryModel::query()->find($categoryId);

        if (! $category) {
            return [];
        }

        $ids = [$category->id];

        $childIds = CategoryModel::query()
            ->where('parent_id', $category->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $ids = array_merge($ids, $childIds);

        if (! empty($childIds)) {
            $grandChildIds = CategoryModel::query()
                ->whereIn('parent_id', $childIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $ids = array_merge($ids, $grandChildIds);
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }
}