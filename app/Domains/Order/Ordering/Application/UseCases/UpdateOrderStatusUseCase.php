<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Engagement\Mission\Application\Services\MissionService;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Mail\OrderCancelledMail;
use App\Domains\Order\Ordering\Infrastructure\Mail\OrderConfirmedMail;
use App\Domains\Order\Ordering\Infrastructure\Mail\OrderDeliveredMail;
use App\Domains\Order\Ordering\Infrastructure\Mail\OrderShippedMail;
use App\Domains\Identity\User\Domain\Entities\User;
use DomainException;
use Illuminate\Support\Facades\Mail;

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

    public function execute(int $orderId, string $status, ?string $reason = null): void
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

        $this->sendOrderEmail($order, $status, $reason);

        if ($status === 'completed') {
            $this->missionService->recordEvent($order->userId, 'order_completed', 1, [
                'order_id' => $orderId,
                'order_type' => $order->orderType,
            ]);
        }
    }

    private function sendOrderEmail(object $order, string $status, ?string $reason): void
    {
        try {
            $buyer = User::find($order->userId);

            if (! $buyer || empty($buyer->email)) {
                return;
            }

            $buyerName = $buyer->name ?? 'Pelanggan';

            match ($status) {
                'pending' => Mail::to($buyer->email)->send(new OrderConfirmedMail($order, $buyerName)),
                'shipped' => Mail::to($buyer->email)->send(new OrderShippedMail($order, $buyerName, 'TRX-' . strtoupper(substr(md5((string) $order->id), 0, 8)), 'Standard')),
                'received' => Mail::to($buyer->email)->send(new OrderDeliveredMail($order, $buyerName)),
                'cancelled' => Mail::to($buyer->email)->send(new OrderCancelledMail($order, $buyerName, $reason ?? 'Dibatalkan oleh sistem')),
                default => null,
            };
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengirim email order', [
                'order_id' => $order->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
