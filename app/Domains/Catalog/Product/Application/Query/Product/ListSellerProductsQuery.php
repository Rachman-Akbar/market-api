<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;

final class ListSellerProductsQuery
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(string $sellerId, array $filters = []): LengthAwarePaginator
    {
        $filters['seller_id'] = $sellerId;

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->products->paginate($filters, $perPage);
    }
}
