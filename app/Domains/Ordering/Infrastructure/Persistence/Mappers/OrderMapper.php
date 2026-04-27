<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Mappers;

use App\Domains\Ordering\Domain\Entities\Order;
use App\Domains\Ordering\Domain\ValueObjects\Money;
use App\Domains\Ordering\Domain\ValueObjects\OrderNumber;
use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use App\Domains\Ordering\Domain\ValueObjects\PaymentStatus;
use App\Domains\Ordering\Domain\ValueObjects\ShippingAddress;
use App\Domains\Ordering\Infrastructure\Persistence\Models\OrderModel;

final readonly class OrderMapper
{
    public function __construct(
        private OrderItemMapper $itemMapper = new OrderItemMapper(),
        private OrderStatusHistoryMapper $historyMapper = new OrderStatusHistoryMapper(),
    ) {
    }

    public function toEntity(OrderModel $model): Order
    {
        $model->loadMissing(['items', 'histories']);
        $currency = (string) $model->currency;

        return new Order(
            id: (int) $model->id,
            orderNumber: new OrderNumber((string) $model->order_number),
            userId: (int) $model->user_id,
            status: new OrderStatus((string) $model->status),
            paymentStatus: new PaymentStatus((string) $model->payment_status),
            shippingAddress: ShippingAddress::fromArray((array) $model->shipping_address),
            items: $model->items->map(fn ($item) => $this->itemMapper->toEntity($item))->all(),
            subtotal: new Money((float) $model->subtotal, $currency),
            shippingCost: new Money((float) $model->shipping_cost, $currency),
            discountTotal: new Money((float) $model->discount_total, $currency),
            taxTotal: new Money((float) $model->tax_total, $currency),
            grandTotal: new Money((float) $model->grand_total, $currency),
            notes: $model->notes,
            paymentMethod: $model->payment_method,
            histories: $model->histories->map(fn ($history) => $this->historyMapper->toEntity($history))->all(),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function fillModel(Order $entity, ?OrderModel $model = null): OrderModel
    {
        $model ??= new OrderModel();
        $model->order_number = $entity->orderNumber()->value();
        $model->user_id = $entity->userId();
        $model->status = $entity->status()->value();
        $model->payment_status = $entity->paymentStatus()->value();
        $model->currency = $entity->grandTotal()->currency();
        $model->subtotal = $entity->subtotal()->toDatabase();
        $model->shipping_cost = $entity->shippingCost()->toDatabase();
        $model->discount_total = $entity->discountTotal()->toDatabase();
        $model->tax_total = $entity->taxTotal()->toDatabase();
        $model->grand_total = $entity->grandTotal()->toDatabase();
        $model->shipping_address = $entity->shippingAddress()->toArray();
        $model->notes = $entity->notes();
        $model->payment_method = $entity->paymentMethod();

        return $model;
    }
}
