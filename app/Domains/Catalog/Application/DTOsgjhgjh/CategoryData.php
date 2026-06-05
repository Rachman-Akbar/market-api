<?php

namespace App\Domains\Catalog\Application\DTOs;

final class CategoryData
{
    public function __construct(
        public ?int $id,
        public ?int $catalogGroupId,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public ?string $coverImageUrl = null,
        public ?int $productsCount = null,
        public bool $isActive = true,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id(),
            catalogGroupId: $entity->catalogGroupId(),
            name: $entity->name(),
            slug: $entity->slug(),
            description: $entity->description(),
            imageUrl: $entity->imageUrl(),
            coverImageUrl: $entity->coverImageUrl(),
            productsCount: $entity->productsCount(),
            isActive: $entity->isActive(),
        );
    }
}