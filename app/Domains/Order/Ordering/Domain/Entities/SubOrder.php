<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Domain\Entities;

final class SubOrder
{
    /**
     * @param OrderItem[] $items
     */
    public function __construct(
        public ?int $id,
        public int $storeId,
        public string $subOrderNumber,
        public float $totalItemsPrice,
        public float $shippingCost,
        public ?string $courier,
        public string $destinationId,
        public string $status,
        public ?string $trackingNumber,
        public array $items = []
    ) {}
}
