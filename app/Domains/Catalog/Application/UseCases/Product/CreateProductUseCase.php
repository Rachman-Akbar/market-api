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
            id: null,
            storeId: $data['store_id'] ?? null,
            categoryId: $data['category_id'] ?? null,
            sellerId: $userId,
            name: $data['name'],
            slug: $data['slug'] ?? Str::slug($data['name']),
            description: $data['description'] ?? null,
            price: (float) $data['price'],
            stock: (int) ($data['stock'] ?? 0),
            thumbnail: $data['thumbnail'] ?? null,
            status: $data['status'] ?? 'draft',
        );

        return $this->repository->save($product);
    }
}