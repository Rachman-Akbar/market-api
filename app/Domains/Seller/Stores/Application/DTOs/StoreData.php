<?php

namespace App\Domains\Seller\Stores\Application\DTOs;

final class StoreData
{
    public function __construct(
        public ?int $id,
        public string $userId,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $logo = null,
        public bool $isActive = true,
        public ?StoreDetailData $detail = null,
    ) {}

    /**
     * @param \App\Domains\Seller\Stores\Domain\Entities\Store $entity
     */
    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id(),
            userId: $entity->userId(),
            name: $entity->name(),
            slug: $entity->slug(),
            description: $entity->description(),
            logo: $entity->logo(),
            isActive: $entity->isActive(),
            // Jalankan ini jika Entity Store kamu memiliki relasi method detail() ke StoreDetail Entity
            detail: method_exists($entity, 'detail') && $entity->detail() 
                ? StoreDetailData::fromEntity($entity->detail()) 
                : null,
        );
    }
}