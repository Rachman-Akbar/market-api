<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Resources;

use App\Domains\Ordering\Domain\Entities\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

final class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;

        return [
            'id' => $order->id(),
            'order_number' => $order->orderNumber()->value(),
            'user_id' => $order->userId(),
            'status' => $order->status()->value(),
            'payment_status' => $order->paymentStatus()->value(),
            'shipping_address' => $order->shippingAddress()->toArray(),
            'items' => OrderItemResource::collection(new Collection($order->items())),
            'subtotal' => $order->subtotal(),
            'shipping_cost' => $order->shippingCost(),
            'discount_total' => $order->discountTotal(),
            'tax_total' => $order->taxTotal(),
            'grand_total' => $order->grandTotal(),
            'payment_method' => $order->paymentMethod(),
            'notes' => $order->notes(),
            'histories' => OrderStatusHistoryResource::collection(new Collection($order->histories())),
            'created_at' => $order->createdAt()?->format(DATE_ATOM),
            'updated_at' => $order->updatedAt()?->format(DATE_ATOM),
        ];
    }
}
