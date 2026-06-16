<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Application\Queries;

use App\Domains\Catalog\Category\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListProductsByCategoryPathQuery
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function execute(
        string $path,
        array $filters = []
    ): LengthAwarePaginator {

        $category = $this->categoryRepository->findByPath($path);

        if (! $category) {
            abort(404, 'Category not found.');
        }

        $includeDescendants = (bool) (
            $filters['include_descendants'] ?? false
        );

        return $this->productRepository->paginateByCategory(
            categoryId: $category->id(),
            filters: $filters,
            includeDescendants: $includeDescendants
        );
    }
}


