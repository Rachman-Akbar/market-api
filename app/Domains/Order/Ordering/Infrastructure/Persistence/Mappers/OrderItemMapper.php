<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Mappers;

use App\Domains\Ordering\Domain\Entities\OrderItem;
use App\Domains\Ordering\Domain\ValueObjects\Money;
use App\Domains\Ordering\Infrastructure\Persistence\Models\OrderItemModel;

final class OrderItemMapper
{
    public function toEntity(OrderItemModel $model): OrderItem
    {
        return new OrderItem(
            id: (int) $model->id,
            productId: (int) $model->product_id,
            productName: (string) $model->product_name,
            sku: $model->sku,
            quantity: (int) $model->quantity,
            unitPrice: new Money((float) $model->unit_price, (string) $model->currency),
            subtotal: new Money((float) $model->subtotal, (string) $model->currency),
        );
    }

    public function toModel(OrderItem $entity, ?OrderItemModel $model = null): OrderItemModel
    {
        $model ??= new OrderItemModel();
        $model->product_id = $entity->productId();
        $model->product_name = $entity->productName();
        $model->sku = $entity->sku();
        $model->quantity = $entity->quantity();
        $model->currency = $entity->unitPrice()->currency();
        $model->unit_price = $entity->unitPrice()->toDatabase();
        $model->subtotal = $entity->subtotal()->toDatabase();

        return $model;
    }
}
