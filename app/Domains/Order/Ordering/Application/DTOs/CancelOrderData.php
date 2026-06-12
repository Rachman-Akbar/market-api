<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\DTOs;

final readonly class CancelOrderData
{
    public function __construct(
        public int|string $orderIdentifier,
        public int $cancelledBy,
        public ?string $reason = null,
        public bool $canManageAllOrders = false,
    ) {
    }
}
