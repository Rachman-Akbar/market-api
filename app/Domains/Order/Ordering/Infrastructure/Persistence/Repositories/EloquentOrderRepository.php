<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Ordering\Domain\Entities\Order as DomainOrder;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderItemModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(DomainOrder $order): DomainOrder
    {
        return DB::transaction(function () use ($order) {
            $orderModel = OrderModel::create([
                'order_number'        => $order->orderNumber,
                'user_id'             => $order->userId,
                'voucher_id'          => $order->voucherId,
                'total_amount'        => $order->totalAmount,
                'discount_amount'     => $order->discountAmount,
                'shipping_cost'       => $order->shippingCost,
                'status'              => $order->status,
                'payment_status'      => $order->paymentStatus,
                'payment_method'      => $order->paymentMethod,
                'midtrans_snap_token' => $order->snapToken,
                'shipping_address'    => $order->shippingAddress,
                'destination_id'      => $order->destinationId,
                'courier'             => $order->courier,
            ]);

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

    public function update(DomainOrder $order): void
    {
        OrderModel::where('id', $order->id)->update([
            'status'              => $order->status,
            'payment_status'      => $order->paymentStatus,
            'midtrans_snap_token' => $order->snapToken,
        ]);
    }

    public function findById(int $id): ?DomainOrder
    {
        $model = OrderModel::with('items')->find($id);
        if (!$model) return null;
        return $this->toDomainEntity($model);
    }

    public function findByOrderNumber(string $orderNumber): ?DomainOrder
    {
        $model = OrderModel::with('items')->where('order_number', $orderNumber)->first();
        if (!$model) return null;
        return $this->toDomainEntity($model);
    }

    public function getByUserId(string $userId): array
    {
        $models = OrderModel::with('items')->where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    /**
     * Helper Mapper internal (Model -> Entity Domain)
     */
    private function toDomainEntity(OrderModel $model): DomainOrder
    {
        $domainItems = $model->items->map(function ($item) {
            return new DomainOrderItem(
                id: $item->id,
                productId: $item->product_id,
                storeId: $item->store_id,
                productName: $item->product_name,
                sku: $item->sku,
                price: $item->price,
                quantity: $item->quantity
            );
        })->toArray();

        return new DomainOrder(
            id: $model->id,
            orderNumber: $model->order_number,
            userId: $model->user_id,
            totalAmount: (float) $model->total_amount,
            shippingCost: (float) $model->shipping_cost,
            discountAmount: (float) $model->discount_amount,
            status: $model->status,
            paymentStatus: $model->payment_status,
            paymentMethod: $model->payment_method,
            snapToken: $model->midtrans_snap_token,
            shippingAddress: $model->shipping_address,
            destinationId: $model->destination_id,
            courier: $model->courier,
            items: $domainItems,
            voucherId: $model->voucher_id
        );
    }
}
