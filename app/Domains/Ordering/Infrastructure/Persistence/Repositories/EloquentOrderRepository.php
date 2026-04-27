<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Repositories;

use App\Domains\Ordering\Domain\Entities\Order;
use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Ordering\Infrastructure\Persistence\Mappers\OrderItemMapper;
use App\Domains\Ordering\Infrastructure\Persistence\Mappers\OrderMapper;
use App\Domains\Ordering\Infrastructure\Persistence\Mappers\OrderStatusHistoryMapper;
use App\Domains\Ordering\Infrastructure\Persistence\Models\OrderItemModel;
use App\Domains\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\Ordering\Infrastructure\Persistence\Models\OrderStatusHistoryModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private OrderMapper $mapper,
        private OrderItemMapper $itemMapper,
        private OrderStatusHistoryMapper $historyMapper,
    ) {
    }

    public function create(Order $order): Order
    {
        $model = $this->mapper->fillModel($order);
        $model->save();

        foreach ($order->items() as $item) {
            $itemModel = $this->itemMapper->toModel($item);
            $model->items()->save($itemModel);
        }

        foreach ($order->histories() as $history) {
            $historyModel = $this->historyMapper->toModel($history);
            $model->histories()->save($historyModel);
        }

        return $this->findById((int) $model->id);
    }

    public function save(Order $order): Order
    {
        $model = OrderModel::query()->whereKey($order->id())->firstOrFail();
        $this->mapper->fillModel($order, $model)->save();

        foreach ($order->items() as $item) {
            $itemModel = $item->id()
                ? OrderItemModel::query()->whereKey($item->id())->firstOrNew()
                : new OrderItemModel();

            $itemModel = $this->itemMapper->toModel($item, $itemModel);
            $model->items()->save($itemModel);
        }

        foreach ($order->histories() as $history) {
            if ($history->id()) {
                continue;
            }

            $historyModel = $this->historyMapper->toModel($history, new OrderStatusHistoryModel());
            $model->histories()->save($historyModel);
        }

        return $this->findById((int) $model->id);
    }

    public function findById(int $id): ?Order
    {
        $model = OrderModel::query()
            ->with(['items', 'histories'])
            ->whereKey($id)
            ->first();

        return $model ? $this->mapper->toEntity($model) : null;
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $model = OrderModel::query()
            ->with(['items', 'histories'])
            ->where('order_number', $orderNumber)
            ->first();

        return $model ? $this->mapper->toEntity($model) : null;
    }

    public function findByIdentifier(int|string $identifier): ?Order
    {
        if (is_numeric($identifier)) {
            return $this->findById((int) $identifier);
        }

        return $this->findByOrderNumber((string) $identifier);
    }

    public function paginateForUser(?int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = OrderModel::query()->with(['items', 'histories'])->latest('id');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $paginator = $query->paginate(max(1, min($perPage, 100)));
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (OrderModel $model): Order => $this->mapper->toEntity($model))
        );

        return $paginator;
    }
}
