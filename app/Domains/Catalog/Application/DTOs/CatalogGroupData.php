<?php

namespace App\Domains\Catalog\Application\DTOs;

final class CatalogGroupData
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public ?string $coverImageUrl = null,
        public bool $isActive = true,
        public array $categories = [],
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id(),
            name: $entity->name(),
            slug: $entity->slug(),
            description: $entity->description(),
            imageUrl: $entity->imageUrl(),
            coverImageUrl: $entity->coverImageUrl(),
            isActive: $entity->isActive(),
            categories: $entity->categories(),
        );
    }
}