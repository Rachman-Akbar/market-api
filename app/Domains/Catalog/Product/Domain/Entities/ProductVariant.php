<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Domain\Entities;

final class ProductVariant
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $productId,
        private readonly string $sku,
        private readonly string $name,
        private readonly float $price,
        private readonly int $stock,
        private readonly bool $isDefault,
        private readonly array $values = [],
        private readonly ?string $createdAt = null,
        private readonly ?string $updatedAt = null
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function values(): array
    {
        return $this->values;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
