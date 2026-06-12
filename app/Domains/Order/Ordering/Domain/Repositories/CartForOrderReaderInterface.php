<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Repositories;

interface CartForOrderReaderInterface
{
    public function getActiveCartForUser(string $userId): ?array;

    public function markAsOrdered(int $cartId, int $orderId): void;
}