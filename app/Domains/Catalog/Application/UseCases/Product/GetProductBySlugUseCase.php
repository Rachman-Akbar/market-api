<?php

namespace App\Domains\Catalog\Application\UseCases\Product;

use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

final class GetProductBySlugUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(string $slug): ?Product
    {
        return $this->products->findBySlug($slug);
    }
}