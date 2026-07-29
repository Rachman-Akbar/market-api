<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\Product;

use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

final class ListSellerProductsQuery
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(string $sellerId, array $filters = []): Collection
    {
        $filters['seller_id'] = $sellerId;
        $filters['include_inactive'] = true;

        return $this->products->getAll($filters);
    }
}
