<?php

declare(strict_types=1);

namespace App\Domains\Order\Cart\Domain\ValueObjects;

final class VariantDetails
{
    /**
     * @param array<string, string> $attributes
     */
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $sku,
        private readonly Money $price,
        private readonly array $attributes = []
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}