<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Repositories;

use App\Domains\Ordering\Domain\Entities\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(Order $order): Order;

    public function save(Order $order): Order;

    public function findById(int $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function findByIdentifier(int|string $identifier): ?Order;

    public function paginateForUser(?int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
