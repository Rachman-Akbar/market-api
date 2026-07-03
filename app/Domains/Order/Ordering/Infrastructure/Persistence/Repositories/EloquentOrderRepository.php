<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Ordering\Domain\Entities\Order as DomainOrder;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderItemModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Mappers\OrderMapper;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    private OrderMapper $mapper;

    public function __construct()
    {
        $this->mapper = new OrderMapper();
    }

    public function create(DomainOrder $order): DomainOrder
    {
        return DB::transaction(function () use ($order) {
            // Gunakan Mapper untuk generate array insert
            $orderModel = OrderModel::create($this->mapper->toPersistenceArray($order));

            $savedDomainItems = [];

            foreach ($order->items as $item) {
                $itemModel = OrderItemModel::create([
                    'order_id'     => $orderModel->id,
                    'product_id'   => $item->productId,
                    'store_id'     => $item->storeId,
                    'product_name' => $item->productName,
                    'sku'          => $item->sku,
                    'price'        => $item->price,
                    'quantity'     => $item->quantity,
                ]);

                $savedDomainItems[] = new DomainOrderItem(
                    id: $itemModel->id,
                    productId: $item->productId,
                    storeId: $item->storeId,
                    productName: $item->productName,
                    sku: $item->sku,
                    price: $item->price,
                    quantity: $item->quantity
                );
            }

            $order->id = $orderModel->id;
            $order->items = $savedDomainItems;

            return $order;
        });
    }

    public function findById(int $id): ?DomainOrder
    {
        $model = OrderModel::with('items')->find($id);
        if (!$model) return null;

        return $this->mapper->toDomain($model);
    }

    public function getByUserId(string $userId): array
    {
        $models = OrderModel::with('items')->where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->mapper->toDomain($model))->toArray();
    }
}