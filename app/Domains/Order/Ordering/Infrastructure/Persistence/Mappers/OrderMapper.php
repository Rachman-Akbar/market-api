<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Mappers;

use App\Domains\Order\Ordering\Domain\Entities\Order as DomainOrder;
use App\Domains\Order\Ordering\Domain\Entities\SubOrder as DomainSubOrder;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;

final class OrderMapper
{
    /**
     * Mengubah Eloquent Model (DB) menjadi Domain Entity (Core Logic)
     */
    public function toDomain(OrderModel $model): DomainOrder
    {
        $subOrders = [];

        if ($model->relationLoaded('subOrders')) {
            $subOrders = $model->subOrders->map(function ($subOrder) {

                $items = [];
if ($subOrder->relationLoaded('items')) {
    // TAMBAHKAN use ($subOrder) di sini agar variabel $subOrder terbaca di dalam scope
    $items = $subOrder->items->map(function ($item) use ($subOrder) {
        return new DomainOrderItem(
            id: (int) $item->id,
            productId: (int) $item->product_id,
            storeId: (int) $subOrder->store_id,
            productName: (string) $item->product_name,
            sku: (string) $item->sku,
            price: (float) $item->price,
            quantity: (int) $item->quantity
        );
    })->toArray();
}

                return new DomainSubOrder(
                    id: (int) $subOrder->id,
                    storeId: (int) $subOrder->store_id,
                    subOrderNumber: (string) $subOrder->sub_order_number,
                    totalItemsPrice: (float) $subOrder->total_items_price,
                    shippingCost: (float) $subOrder->shipping_cost,
                    courier: $subOrder->courier ? (string) $subOrder->courier : null,
                    destinationId: (string) $subOrder->destination_id,
                    status: (string) $subOrder->status,
                    trackingNumber: $subOrder->tracking_number ? (string) $subOrder->tracking_number : null,
                    items: $items
                );
            })->toArray();
        }

        return new DomainOrder(
            id: (int) $model->id,
            orderNumber: (string) $model->order_number,
            userId: (string) $model->user_id,
            voucherId: $model->voucher_id ? (int) $model->voucher_id : null,
            totalAmount: (float) $model->total_amount,
            discountAmount: (float) $model->discount_amount,
            status: (string) $model->status,
            paymentStatus: (string) $model->payment_status,
            paymentMethod: $model->payment_method ? (string) $model->payment_method : null,
            snapToken: $model->midtrans_snap_token ? (string) $model->midtrans_snap_token : null,
            shippingAddress: (string) $model->shipping_address,
            subOrders: $subOrders
        );
    }

    /**
     * Mentransfer data dari Domain Entity ke Eloquent Model array untuk tabel `orders` parent
     */
    public function toPersistenceArray(DomainOrder $entity): array
    {
        return [
            'order_number'        => $entity->orderNumber,
            'user_id'             => $entity->userId,
            'voucher_id'          => $entity->voucherId,
            'total_amount'        => $entity->totalAmount,
            'discount_amount'     => $entity->discountAmount,
            'status'              => $entity->status,
            'payment_status'      => $entity->paymentStatus,
            'payment_method'      => $entity->paymentMethod,
            'midtrans_snap_token' => $entity->snapToken,
            'shipping_address'    => $entity->shippingAddress,
        ];
    }
}
