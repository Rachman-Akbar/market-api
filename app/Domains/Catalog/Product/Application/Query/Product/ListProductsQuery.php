<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;

final class ListProductsQuery
{
    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 60;

    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = $this->resolvePerPage($filters);

        $filters['status'] = $filters['status'] ?? 'published';
        $filters['is_active'] = true;
        $filters['include'] = $filters['include'] ?? 'summary';

        $categorySlug = $filters['category_slug']
            ?? $filters['category']
            ?? null;

        if (is_string($categorySlug) && trim($categorySlug) !== '') {
            return $this->products->findPublishedByCategorySlug(
                categorySlug: trim($categorySlug),
                filters: $filters,
                perPage: $perPage
            );
        }

        return $this->products->paginate($filters, $perPage);
    }

    private function resolvePerPage(array $filters): int
    {
        $raw = $filters['per_page'] ?? $filters['limit'] ?? self::DEFAULT_PER_PAGE;
        $perPage = (int) $raw;

        return max(1, min($perPage, self::MAX_PER_PAGE));
    }
}
