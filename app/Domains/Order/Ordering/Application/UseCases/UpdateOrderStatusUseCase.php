<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Engagement\Mission\Application\Services\MissionService;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use DomainException;

class UpdateOrderStatusUseCase
{
    private const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['received', 'completed'],
        'received' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private MissionService $missionService
    ) {}

    public function execute(int $orderId, string $status): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (! $order) {
            throw new DomainException('Order tidak ditemukan.');
        }

        if ($order->status === $status) {
            return;
        }

        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($status, $allowed, true)) {
            throw new DomainException("Perubahan status dari {$order->status} ke {$status} tidak diizinkan.");
        }

        if ($status === 'processing' && $order->paymentMethod === 'midtrans' && $order->paymentStatus !== 'paid') {
            throw new DomainException('Order Midtrans belum memiliki pembayaran yang berhasil.');
        }

        $order->status = $status;

        if ($status === 'received') {
            $order->receivedAt = now()->toDateTimeString();
        }

        $this->orderRepository->update($order);

        if ($status === 'completed') {
            $this->missionService->recordEvent($order->userId, 'order_completed', 1, [
                'order_id' => $orderId,
                'order_type' => $order->orderType,
            ]);
        }
    }
}
