<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Domain\Entities;

final class ProductVariant
{
    public function __construct(
        private ?int $id,
        private int $productId,
        private ?string $sku,
        private string $name,
        private float $price,
        private int $stock,
        private bool $isDefault = false,
        private array $values = [],
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function sku(): ?string
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
}
