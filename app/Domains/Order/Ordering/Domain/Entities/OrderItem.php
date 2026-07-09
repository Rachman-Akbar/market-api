<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Domain\Entities;

final class OrderItem
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public int $storeId,
        public string $productName,
        public string $sku,
        public float $price,
        public int $quantity
    ) {}

    public function getSubTotal(): float
    {
        return $this->price * $this->quantity;
    }
}
