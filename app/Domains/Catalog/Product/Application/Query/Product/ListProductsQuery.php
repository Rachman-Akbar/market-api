<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;

final class ListProductsQuery
{
    private const BUYER_PER_PAGE = 4;

    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = self::BUYER_PER_PAGE;

        $filters['status'] = $filters['status'] ?? 'published';
        $filters['is_active'] = true;

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
}
