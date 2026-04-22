<?php

namespace App\Domains\Catalog\Application\DTOs;

class StoreDetailData
{
    public function __construct(
        public ?int $id,
        public int $store_id,
        public string $description,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id ?? null,
            store_id: $entity->store_id,
            description: $entity->description,
            created_at: $entity->created_at ?? null,
            updated_at: $entity->updated_at ?? null,
        );
    }
}