<?php

namespace App\Domains\Catalog\Domain\Entities;

final class ProductImage
{
    public function __construct(
        private ?int $id,
        private int $productId,
        private string $imageUrl,
        private bool $isPrimary = false,
    ) {}

    public function id(): ?int { return $this->id; }
    public function productId(): int { return $this->productId; }
    public function imageUrl(): string { return $this->imageUrl; }
    public function isPrimary(): bool { return $this->isPrimary; }
}