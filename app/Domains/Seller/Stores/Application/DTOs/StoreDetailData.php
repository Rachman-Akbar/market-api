<?php

namespace App\Domains\Stores\Application\DTOs;

final class StoreDetailData
{
    public function __construct(
        public ?int $id,
        public int $storeId,
        public ?string $description = null,
        public ?string $address = null,
        public ?string $phone = null,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id(),
            storeId: $entity->storeId(),
            description: $entity->description(),
            address: $entity->address(),
            phone: $entity->phone(),
        );
    }
}
