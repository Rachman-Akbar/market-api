<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    /**
     * Catalog listing
     *
     * @return LengthAwarePaginator<Product>
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /**
     * Find product by ID
     */
    public function findById(string $id): ?Product;

    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Create new product
     */
    public function create(array $data): Product;

    /**
     * Update product
     */
    public function update(string $id, array $data): Product;

    /**
     * Delete product
     */
    public function delete(string $id): bool;
}
