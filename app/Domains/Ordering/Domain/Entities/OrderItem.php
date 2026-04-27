<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Entities;

use App\Domains\Ordering\Domain\ValueObjects\Money;
use InvalidArgumentException;

final class OrderItem
{
    public function __construct(
        private ?int $id,
        private int $productId,
        private string $productName,
        private ?string $sku,
        private int $quantity,
        private Money $unitPrice,
        private Money $subtotal,
    ) {
        if ($productId <= 0) {
            throw new InvalidArgumentException('Product id must be greater than zero.');
        }

        if (trim($productName) === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }
    }

    public static function create(
        int $productId,
        string $productName,
        ?string $sku,
        int $quantity,
        Money $unitPrice,
    ): self {
        return new self(
            id: null,
            productId: $productId,
            productName: $productName,
            sku: $sku,
            quantity: $quantity,
            unitPrice: $unitPrice,
            subtotal: $unitPrice->multiply($quantity),
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function markPersisted(int $id): void
    {
        $this->id = $id;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function subtotal(): Money
    {
        return $this->subtotal;
    }

    public function stockPayload(): array
    {
        return [
            'product_id' => $this->productId(),
            'quantity' => $this->quantity(),
        ];
    }
}
