<?php

declare(strict_types=1);

namespace App\Domains\Order\Cart\Domain\ValueObjects;

final class VariantDetails
{
    /**
     * @param array<string, string> $attributes
     */
    public function __construct(
    private readonly int $id,          // Ini ID Varian (misal: 3)
    private readonly int $productId,   // Ini ID Produk Utama (misal: 1)
    private readonly string $name,
    private readonly int $storeId,
    private readonly string $sku,
    private readonly Money $price,
    private readonly array $attributes = []
) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
