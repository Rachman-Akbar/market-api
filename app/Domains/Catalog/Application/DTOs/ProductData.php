<?php

namespace App\Domains\Catalog\Application\DTOs;

final class ProductData
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public float $price,
        public int $stock,
        public ?string $thumbnail,
        public string $status,
        public ?int $categoryId,
        public ?int $storeId,
        public string $sellerId,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id(),
            name: $entity->name(),
            slug: $entity->slug(),
            description: $entity->description(),
            price: $entity->price(),
            stock: $entity->stock(),
            thumbnail: $entity->thumbnail(),
            status: $entity->status(),
            categoryId: $entity->categoryId(),
            storeId: $entity->storeId(),
            sellerId: $entity->sellerId(),
        );
    }
}