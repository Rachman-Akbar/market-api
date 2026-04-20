<?php

namespace App\Domains\Catalog\Infrastructure\Persistence;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->latest()->paginate(15);
    }

    public function findById(string $id)
    {
        return Product::find($id);
    }

    public function findBySlug(string $slug)
    {
        return Product::where('slug', $slug)->first();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(string $id, array $data)
    {
        $product = Product::findOrFail($id);

        $product->update($data);

        return $product;
    }

    public function delete(string $id): bool
    {
        $product = Product::findOrFail($id);

        return $product->delete();
    }
}