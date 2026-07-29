<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\Product;

use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

final class ListProductsQuery
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(array $filters = []): Collection
    {
        $filters['status'] = $filters['status'] ?? 'published';
        $filters['is_active'] = true;
        $filters['include'] = $filters['include'] ?? 'summary';

        $categorySlug = $filters['category_slug']
            ?? $filters['category']
            ?? null;

        if (is_string($categorySlug) && trim($categorySlug) !== '') {
            return $this->products->findPublishedByCategorySlug(
                categorySlug: trim($categorySlug),
                filters: $filters
            );
        }

        return $this->products->getAll($filters);
    }
}
