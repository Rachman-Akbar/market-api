<?php

namespace App\Domains\Catalog\Application\UseCases\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Product;
use Illuminate\Support\Str;

final class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(string $userId, array $data): Product
    {
        $product = new Product(
            id: (string) Str::uuid(),
            userId: $userId,
            name: $data['name'],
            slug: Str::slug($data['name']),
            description: $data['description'] ?? null,
            price: $data['price'],
            status: $data['status'] ?? 'draft'
        );

        return $this->repository->save($product);
    }
}