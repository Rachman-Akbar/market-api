<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Mappers;

use App\Domains\Order\Ordering\Domain\Entities\Order as DomainOrder;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;

final class OrderMapper
{
    /**
     * Mengubah Eloquent Model (DB) menjadi Domain Entity (Core Logic)
     */
    public function toDomain(OrderModel $model): DomainOrder
    {
        $items = $model->items->map(function ($item) {
            return new DomainOrderItem(
                id: (int) $item->id,
                productId: (int) $item->product_id,
                storeId: (int) $item->store_id,
                productName: (string) $item->product_name,
                sku: (string) $item->sku,
                price: (float) $item->price,
                quantity: (int) $item->quantity
            );
        })->toArray();

        return new DomainOrder(
            id: (int) $model->id,
            orderNumber: (string) $model->order_number,
            userId: (string) $model->user_id,
            totalAmount: (float) $model->total_amount,
            shippingCost: (float) $model->shipping_cost, // <--- TAMBAHKAN INI
            discountAmount: (float) $model->discount_amount,
            status: (string) $model->status,
            paymentStatus: (string) $model->payment_status, // <--- TAMBAHKAN INI
            paymentMethod: $model->payment_method ? (string) $model->payment_method : null, // <--- TAMBAHKAN INI
            snapToken: $model->midtrans_snap_token ? (string) $model->midtrans_snap_token : null, // <--- TAMBAHKAN INI
            shippingAddress: (string) $model->shipping_address,
            destinationId: (string) $model->destination_id, // <--- TAMBAHKAN INI
            courier: $model->courier ? (string) $model->courier : null, // <--- TAMBAHKAN INI
            items: $items,
            voucherId: $model->voucher_id ? (int) $model->voucher_id : null
        );
    }

    /**
     * Mentransfer data dari Domain Entity ke Eloquent Model array untuk disimpan ke DB
     */
    public function toPersistenceArray(DomainOrder $entity): array
    {
        return [
            'order_number'        => $entity->orderNumber,
            'user_id'             => $entity->userId,
            'voucher_id'          => $entity->voucherId,
            'total_amount'        => $entity->totalAmount,
            'discount_amount'     => $entity->discountAmount,
            'shipping_cost'       => $entity->shippingCost, // <--- TAMBAHKAN INI
            'status'              => $entity->status,
            'payment_status'      => $entity->paymentStatus, // <--- TAMBAHKAN INI
            'payment_method'      => $entity->paymentMethod, // <--- TAMBAHKAN INI
            'midtrans_snap_token' => $entity->snapToken, // <--- TAMBAHKAN INI
            'shipping_address'    => $entity->shippingAddress,
            'destination_id'      => $entity->destinationId, // <--- TAMBAHKAN INI
            'courier'             => $entity->courier, // <--- TAMBAHKAN INI
        ];
    }
}
