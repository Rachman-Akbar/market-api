<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\ProductMapper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
            $query->whereHas('categories', function ($query) use ($filters) {
                $query->where('categories.id', $filters['category_id']);
            });
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $paginator = $query->latest()->paginate($perPage);

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

        if (method_exists($product, 'categoryIds')) {
            $categoryIds = $product->categoryIds();

            if (! empty($categoryIds)) {
                $model->categories()->sync($categoryIds);
            }
        }

        $model->load(['primaryCategory', 'categories', 'store', 'images']);

        return ProductMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return ProductModel::where('id', $id)->delete() > 0;
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
        $query = ProductModel::query()
            ->with(['primaryCategory', 'categories', 'store', 'images'])
            ->whereHas('categories', function ($query) use ($categorySlug) {
                $query
                    ->where('categories.slug', $categorySlug)
                    ->orWhere('categories.full_slug', $categorySlug);
            })
            ->where('status', 'published');

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
}
