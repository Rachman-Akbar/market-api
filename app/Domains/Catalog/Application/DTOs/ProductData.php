<?php

namespace App\Domains\Catalog\Application\DTOs;

class ProductData
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
        public ?int $category_id,
        public ?int $store_id,
        public ?string $seller_id,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id ?? null,
            name: $entity->name,
            slug: $entity->slug,
            description: $entity->description ?? null,
            price: $entity->price,
            stock: $entity->stock,
            thumbnail: $entity->thumbnail ?? null,
            status: $entity->status,
            category_id: $entity->category_id ?? null,
            store_id: $entity->store_id ?? null,
            seller_id: $entity->seller_id ?? null,
            created_at: $entity->created_at ?? null,
            updated_at: $entity->updated_at ?? null,
        );
    }
}
