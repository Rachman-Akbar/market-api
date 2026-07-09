<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\UseCases\Order;

use App\Domains\Order\Ordering\Domain\Entities\Order;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class GetOrderDetailUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int|string $identifier, string $authenticatedUserId, bool $canViewAllOrders = false): Order
    {
        // PERBAIKAN: Deteksi identifier apakah ID (integer) atau Order Number (string)
        $order = is_numeric($identifier)
            ? $this->orders->findById((int) $identifier)
            : $this->orders->findByOrderNumber((string) $identifier);

        if (! $order) {
            throw (new ModelNotFoundException())->setModel(Order::class, [$identifier]);
        }

        // Pastikan entity Order kamu memiliki method belongsTo() atau cek manual propertinya di sini
        if (! $canViewAllOrders && method_exists($order, 'belongsTo') && ! $order->belongsTo($authenticatedUserId)) {
            throw new AuthorizationException('You are not allowed to access this order.');
        }

        return $order;
    }
}