<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\DTOs;

final readonly class UpdateOrderStatusData
{
    public function __construct(
        public int|string $orderIdentifier,
        public string $status,
        public int $changedBy,
        public ?string $note = null,
    ) {
    }
}
