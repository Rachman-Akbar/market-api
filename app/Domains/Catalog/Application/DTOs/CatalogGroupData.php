<?php

namespace App\Domains\Catalog\Application\DTOs;

class CatalogGroupData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id,
            name: $entity->name,
            slug: $entity->slug,
            description: $entity->description,
        );
    }
}