<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\UseCases\Order;

use App\Domains\Ordering\Domain\Entities\Order;
use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class GetOrderDetailUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int|string $identifier, int $authenticatedUserId, bool $canViewAllOrders = false): Order
    {
        $order = $this->orders->findByIdentifier($identifier);

        if (! $order) {
            throw (new ModelNotFoundException())->setModel(Order::class, [$identifier]);
        }

        if (! $canViewAllOrders && ! $order->belongsTo($authenticatedUserId)) {
            throw new AuthorizationException('You are not allowed to access this order.');
        }

        return $order;
    }
}
