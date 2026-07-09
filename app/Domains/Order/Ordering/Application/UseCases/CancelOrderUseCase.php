<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use Exception;

class CancelOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function execute(int $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new Exception("Order tidak ditemukan.");
        }

        // Bisnis Aturan / Invariant Validation
        if (in_array($order->status, ['completed', 'cancelled'])) {
            throw new Exception("Order yang sudah selesai atau dibatalkan tidak bisa dibatalkan lagi.");
        }

        $order->status = 'cancelled';
        $order->paymentStatus = 'cancelled'; // Selaraskan status Midtrans jika diperlukan

        $this->orderRepository->update($order);
    }
}
