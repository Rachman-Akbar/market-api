<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Domain\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Product\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function findPublishedByStoreId(int $storeId): Collection;

    public function findPublishedByCategorySlug(
        string $categorySlug,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findPublishedByCategoryPath(
        string $path,
        array $filters,
        bool $includeDescendants,
        int $perPage
    ): LengthAwarePaginator;

    public function paginateByCategory(
        int $categoryId,
        array $filters,
        bool $includeDescendants
    ): LengthAwarePaginator;

    public function save(Product $product): Product;

    public function delete(int $id): bool;
}
