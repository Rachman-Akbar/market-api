<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

final class PpoOperator
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public string $category,
        public ?string $brand = null,
        public ?string $operatorPrefix = null,
        public string $providerName = 'IAK',
        public ?string $iconUrl = null,
        public bool $isActive = true,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
