<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\ProductMapper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = ProductModel::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $paginator = $query->latest()->paginate();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => ProductMapper::toEntity($model))
        );

        return $paginator;
    }

    public function findById(string $id): ?Product
    {
        $model = ProductModel::find($id);

        return $model
            ? ProductMapper::toEntity($model)
            : null;
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = ProductModel::where('slug', $slug)->first();

        return $model
            ? ProductMapper::toEntity($model)
            : null;
    }

    public function save(Product $product): Product
    {
        $model = ProductModel::find($product->id())
            ?? ProductMapper::toModel($product);

        $model->name = $product->name();
        $model->slug = $product->slug();
        $model->description = $product->description();
        $model->price = $product->price();
        $model->status = $product->status();

        $model->save();

        return ProductMapper::toEntity($model);
    }

    public function delete(string $id): bool
    {
        return ProductModel::where('id', $id)->delete() > 0;
    }
}