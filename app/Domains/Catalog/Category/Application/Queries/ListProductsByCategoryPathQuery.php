<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Application\Queries;

use App\Domains\Catalog\Category\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ListProductsByCategoryPathQuery
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function execute(string $path, array $filters = []): Collection
    {
        $category = $this->categoryRepository->findByPath($path);

        if (! $category) {
            throw new NotFoundHttpException('Category not found.');
        }

        $includeDescendants = filter_var(
            $filters['include_descendants'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        return $this->productRepository->findByCategory(
            categoryId: $category->id(),
            filters: $filters,
            includeDescendants: $includeDescendants
        );
    }
}
