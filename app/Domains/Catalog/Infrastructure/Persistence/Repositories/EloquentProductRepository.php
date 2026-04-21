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

        return $query
            ->latest()
            ->paginate()
            ->through(fn ($model) => ProductMapper::toEntity($model));
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

    public function create(array $data): Product
    {
        $model = ProductModel::create($data);

        return ProductMapper::toEntity($model);
    }

    public function update(string $id, array $data): Product
    {
        $model = ProductModel::findOrFail($id);

        $model->update($data);

        return ProductMapper::toEntity($model->fresh());
    }

    public function delete(string $id): bool
    {
        return ProductModel::where('id', $id)->delete() > 0;
    }
}
