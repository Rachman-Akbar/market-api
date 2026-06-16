<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Application\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;

final class ListProductsByCategorySlugQuery
{
    public function __construct(
        private ProductRepositoryInterface $products
    ) {}

    public function execute(
        string $categorySlug,
        array $filters = []
    ): LengthAwarePaginator {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $this->products->findPublishedByCategorySlug(
            categorySlug: $categorySlug,
            filters: $filters,
            perPage: $perPage
        );
    }
}

