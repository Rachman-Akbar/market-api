<?php

namespace App\Domains\Order\Ordering\Domain\Entities;

class OrderItem
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public int $storeId, // Diubah ke storeId
        public string $productName,
        public string $sku,
        public float $price,
        public int $quantity
    ) {}
}
