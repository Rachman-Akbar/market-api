<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\UseCases\Order;

use App\Domains\Ordering\Application\DTOs\UpdateOrderStatusData;
use App\Domains\Ordering\Domain\Entities\Order;
use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Ordering\Domain\Services\OrderStatusTransitionService;
use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use App\Domains\Ordering\Infrastructure\Services\OrderTransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class UpdateOrderStatusUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderStatusTransitionService $transitionService,
        private OrderTransactionService $transaction,
    ) {
    }

    public function execute(UpdateOrderStatusData $data): Order
    {
        return $this->transaction->run(function () use ($data): Order {
            $order = $this->orders->findByIdentifier($data->orderIdentifier);

            if (! $order) {
                throw (new ModelNotFoundException())->setModel(Order::class, [$data->orderIdentifier]);
            }

            $newStatus = new OrderStatus($data->status);
            $this->transitionService->assertCanTransition($order->status(), $newStatus);
            $order->changeStatus($newStatus, $data->note, $data->changedBy);

            return $this->orders->save($order);
        });
    }
}
