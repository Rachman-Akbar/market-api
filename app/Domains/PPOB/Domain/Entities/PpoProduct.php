<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

final class PpoProduct
{
    public function __construct(
        public ?int $id,
        public ?int $operatorId,
        public string $category,
        public string $productType,
        public string $providerProductCode,
        public string $name,
        public ?string $brand,
        public ?string $nominal,
        public float $providerPrice,
        public float $adminFee,
        public float $commission,
        public float $margin,
        public float $sellingPrice,
        public string $status,
        public bool $isAvailable,
        public bool $isActive,
        public ?string $iconUrl = null,
        public ?array $metadata = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
