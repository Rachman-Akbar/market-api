<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

final class ListProductsUseCase
{
    private const DEFAULT_PER_PAGE = 24;
    private const MAX_PER_PAGE = 50;

    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(array $filters = []): LengthAwarePaginator
    {
        $requestedPerPage = isset($filters['per_page'])
            ? (int) $filters['per_page']
            : self::DEFAULT_PER_PAGE;

        $perPage = min(
            max($requestedPerPage, 1),
            self::MAX_PER_PAGE
        );

        $categorySlug = $filters['category_slug']
            ?? $filters['category']
            ?? null;

        if (is_string($categorySlug) && $categorySlug !== '') {
            return $this->products->findPublishedByCategorySlug(
                categorySlug: $categorySlug,
                filters: $filters,
                perPage: $perPage
            );
        }

        return $this->products->paginate($filters, $perPage);
    }
}