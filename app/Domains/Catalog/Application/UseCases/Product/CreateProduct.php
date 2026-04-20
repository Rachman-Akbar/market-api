<?php

namespace App\Domains\Catalog\Application\UseCases;

use Illuminate\Support\Str;
use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(string $sellerId, array $data): Product
    {
        $product = new Product(
            id: null,
            sellerId: $sellerId,
            name: $data['name'],
            slug: Str::slug($data['name']) . '-' . Str::random(5),
            description: $data['description'] ?? null,
            price: $data['price'],
            status: 'draft'
        );

        return $this->repository->save($product);
    }
}