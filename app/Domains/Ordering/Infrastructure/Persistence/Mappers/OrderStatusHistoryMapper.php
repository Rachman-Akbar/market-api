<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Mappers;

use App\Domains\Ordering\Domain\Entities\OrderStatusHistory;
use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use App\Domains\Ordering\Infrastructure\Persistence\Models\OrderStatusHistoryModel;

final class OrderStatusHistoryMapper
{
    public function toEntity(OrderStatusHistoryModel $model): OrderStatusHistory
    {
        return new OrderStatusHistory(
            id: (int) $model->id,
            orderId: (int) $model->order_id,
            fromStatus: $model->from_status ? new OrderStatus((string) $model->from_status) : null,
            toStatus: new OrderStatus((string) $model->to_status),
            note: $model->note,
            changedBy: $model->changed_by ? (int) $model->changed_by : null,
            createdAt: $model->created_at,
        );
    }

    public function toModel(OrderStatusHistory $entity, ?OrderStatusHistoryModel $model = null): OrderStatusHistoryModel
    {
        $model ??= new OrderStatusHistoryModel();
        $model->from_status = $entity->fromStatus()?->value();
        $model->to_status = $entity->toStatus()->value();
        $model->note = $entity->note();
        $model->changed_by = $entity->changedBy();

        if ($entity->orderId()) {
            $model->order_id = $entity->orderId();
        }

        if ($entity->createdAt()) {
            $model->created_at = $entity->createdAt();
        }

        return $model;
    }
}
