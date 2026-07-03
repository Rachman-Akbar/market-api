<?php

namespace App\Domains\Order\Ordering\Domain\Entities;

class Order
{
    public function __construct(
        public ?int $id,
        public string $orderNumber,
        public string $userId,
        public float $totalAmount,
        public string $status,
        public string $shippingAddress,
        public array $items = [],
        public ?int $voucherId = null,         // <--- TAMBAHKAN INI
        public float $discountAmount = 0.00   // <--- TAMBAHKAN INI
    ) {}
}
