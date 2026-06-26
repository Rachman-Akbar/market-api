<?php

namespace App\Domains\Order\Ordering\Domain\Repositories;

use App\Domains\Order\Ordering\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function create(Order $order): Order;
    public function findById(int $id): ?Order;
    public function getByUserId(string $userId): array;
}
