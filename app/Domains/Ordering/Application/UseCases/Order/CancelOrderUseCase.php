<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\UseCases\Order;

use App\Domains\Ordering\Application\DTOs\CancelOrderData;
use App\Domains\Ordering\Domain\Entities\Order;
use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Ordering\Domain\Repositories\ProductStockRepositoryInterface;
use App\Domains\Ordering\Domain\Services\OrderStatusTransitionService;
use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use App\Domains\Ordering\Infrastructure\Services\OrderTransactionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class CancelOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private ProductStockRepositoryInterface $stockRepository,
        private OrderStatusTransitionService $transitionService,
        private OrderTransactionService $transaction,
    ) {
    }

    public function execute(CancelOrderData $data): Order
    {
        return $this->transaction->run(function () use ($data): Order {
            $order = $this->orders->findByIdentifier($data->orderIdentifier);

            if (! $order) {
                throw (new ModelNotFoundException())->setModel(Order::class, [$data->orderIdentifier]);
            }

            if (! $data->canManageAllOrders && ! $order->belongsTo($data->cancelledBy)) {
                throw new AuthorizationException('You are not allowed to cancel this order.');
            }

            $targetStatus = OrderStatus::cancelled();
            $this->transitionService->assertCanTransition($order->status(), $targetStatus);

            $order->changeStatus($targetStatus, $data->reason ?: 'Order cancelled', $data->cancelledBy);
            $saved = $this->orders->save($order);

            $stockPayload = array_map(static fn ($item): array => $item->stockPayload(), $saved->items());
            $this->stockRepository->increaseMany($stockPayload);

            return $saved;
        });
    }
}
