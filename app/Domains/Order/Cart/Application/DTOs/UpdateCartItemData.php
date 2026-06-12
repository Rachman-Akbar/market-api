<?php

declare(strict_types=1);

namespace App\Domains\Cart\Application\DTOs;

final readonly class UpdateCartItemData
{
    public function __construct(
        public string $userId,
        public int $productId,
        public int $quantity,
    ) {
    }
}
