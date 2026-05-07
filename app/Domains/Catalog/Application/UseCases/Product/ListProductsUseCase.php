<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

final class ListProductsUseCase
{
    /**
     * Untuk infinite scroll buyer.
     * Frontend tidak perlu mengirim per_page.
     */
    private const BUYER_PER_PAGE = 4;

    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = self::BUYER_PER_PAGE;

        /**
         * Buyer product listing default hanya tampilkan published.
         * Kalau nanti admin/seller butuh semua status, buat use case terpisah.
         */
        $filters['status'] = $filters['status'] ?? 'published';

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