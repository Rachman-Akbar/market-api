<?php

namespace App\Domains\Catalog\Application\DTOs;

class CategoryData
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id ?? null,
            name: $entity->name,
            slug: $entity->slug,
            created_at: $entity->created_at ?? null,
            updated_at: $entity->updated_at ?? null,
        );
    }
}
