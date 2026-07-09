<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use Exception;

class UpdateOrderStatusUseCase
{
    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function execute(int $orderId, string $status): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new Exception("Order tidak ditemukan.");
        }

        // Mutasi State Objek Domain
        $order->status = $status;

        // Persist via Repository
        $this->orderRepository->update($order);
    }
}
