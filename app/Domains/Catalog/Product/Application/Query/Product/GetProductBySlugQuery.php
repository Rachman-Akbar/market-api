<?php

namespace App\Domains\Catalog\Product\Application\Query\Product;

use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Entities\Product;

final class GetProductBySlugQuery
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(string $slug): ?Product
    {
        return $this->products->findBySlug($slug);
    }
}
