<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use DomainException;

class UpdateOrderStatusUseCase
{
    private const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function execute(int $orderId, string $status): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new DomainException('Order tidak ditemukan.');
        }

        if ($order->status === $status) {
            return;
        }

        $allowed = self::TRANSITIONS[$order->status] ?? [];
        if (!in_array($status, $allowed, true)) {
            throw new DomainException("Perubahan status dari {$order->status} ke {$status} tidak diizinkan.");
        }

        if ($status === 'processing' && $order->paymentMethod === 'midtrans' && $order->paymentStatus !== 'paid') {
            throw new DomainException('Order Midtrans belum memiliki pembayaran yang berhasil.');
        }

        $order->status = $status;
        $this->orderRepository->update($order);
    }
}
