<?php

namespace App\Domains\Order\Ordering\Domain\Repositories;

use App\Domains\Order\Ordering\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function create(Order $order): Order;
    public function update(Order $order): void; // <--- TAMBAHKAN INI untuk update status/token
    public function findById(int $id): ?Order;
    public function findByOrderNumber(string $orderNumber): ?Order; // <--- TAMBAHKAN INI untuk Midtrans Webhook
    public function getByUserId(string $userId): array;
}
