<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\Entities;

use App\Domains\Cart\Domain\ValueObjects\Money;
use App\Domains\Cart\Domain\ValueObjects\Quantity;
use DomainException;

final class CartItem
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $cartId,
        private readonly int $productId,
        private Quantity $quantity,
        private Money $priceSnapshot,
        private string $productNameSnapshot,
        private ?string $productImageSnapshot = null,
    ) {
        if ($productId <= 0) {
            throw new DomainException('Product ID tidak valid.');
        }

        if (trim($productNameSnapshot) === '') {
            throw new DomainException('Nama produk snapshot wajib diisi.');
        }
    }

    public function id(): ?int { return $this->id; }
    public function cartId(): ?int { return $this->cartId; }
    public function productId(): int { return $this->productId; }
    public function quantity(): Quantity { return $this->quantity; }
    public function priceSnapshot(): Money { return $this->priceSnapshot; }
    public function productNameSnapshot(): string { return $this->productNameSnapshot; }
    public function productImageSnapshot(): ?string { return $this->productImageSnapshot; }

    public function increase(Quantity $quantity): void
    {
        $this->quantity = $this->quantity->add($quantity);
    }

    public function changeQuantity(Quantity $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function refreshSnapshot(Money $price, string $name, ?string $image): void
    {
        if (trim($name) === '') {
            throw new DomainException('Nama produk snapshot wajib diisi.');
        }

        $this->priceSnapshot = $price;
        $this->productNameSnapshot = $name;
        $this->productImageSnapshot = $image;
    }

    public function subtotal(): Money
    {
        return $this->priceSnapshot->multiply($this->quantity->value());
    }
}
