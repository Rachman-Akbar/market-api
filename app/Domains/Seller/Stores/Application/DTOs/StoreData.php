<?php

namespace App\Domains\Seller\Stores\Application\DTOs;

final class StoreData
{
    public function __construct(
        public int $id,
        public string $userId,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $shortDescription,
        public ?string $phone,
        public ?string $email,
        public ?string $city,
        public ?string $province,
        public ?string $address,
        public bool $isActive,
        public ?string $logo,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?StoreDetailData $detail = null,
    ) {}

    public static function fromEntity($entity): self
    {
        return new self(
            id: $entity->id(),
            userId: $entity->userId(),
            name: $entity->name(),
            slug: $entity->slug(),
            description: $entity->description(),
            shortDescription: $entity->shortDescription(),
            phone: $entity->phone(),
            email: $entity->email(),
            city: $entity->city(),
            province: $entity->province(),
            address: $entity->address(),
            isActive: $entity->isActive(),
            logo: $entity->logo(),
            createdAt: $entity->createdAt(),
            updatedAt: $entity->updatedAt(),
            detail: method_exists($entity, 'detail') && $entity->detail() 
                ? StoreDetailData::fromEntity($entity->detail()) 
                : null,
        );
    }
}