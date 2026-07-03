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
            status: (string) $model->status,
            shippingAddress: (string) $model->shipping_address,
            items: $items,
            voucherId: $model->voucher_id ? (int) $model->voucher_id : null,
            discountAmount: (float) $model->discount_amount
        );
    }

    /**
     * Mentransfer data dari Domain Entity ke Eloquent Model array untuk disimpan ke DB
     */
    public function toPersistenceArray(DomainOrder $entity): array
    {
        return [
            'order_number'    => $entity->orderNumber,
            'user_id'         => $entity->userId,
            'voucher_id'      => $entity->voucherId,
            'total_amount'    => $entity->totalAmount,
            'discount_amount' => $entity->discountAmount,
            'status'          => $entity->status,
            'shipping_address'=> $entity->shippingAddress,
        ];
    }
}
