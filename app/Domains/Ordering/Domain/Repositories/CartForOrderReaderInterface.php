<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Repositories;

interface CartForOrderReaderInterface
{
    /**
     * Return shape:
     *
     * [
     *   'id' => 1,
     *   'items' => [
     *     [
     *       'product_id' => 10,
     *       'product_name' => 'Product Name',
     *       'sku' => 'SKU-001',
     *       'quantity' => 2,
     *       'unit_price' => 150000,
     *       'currency' => 'IDR',
     *     ],
     *   ],
     * ]
     */
    public function getActiveCartForUser(int $userId): ?array;

    public function markAsOrdered(int $cartId, int $orderId): void;
}
