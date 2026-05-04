<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $order = $this->resource;

        return [
            'id' => $this->read($order, 'id'),
            'order_number' => $this->readValue($this->read($order, 'orderNumber') ?? $this->read($order, 'order_number')),
            'user_id' => $this->read($order, 'userId') ?? $this->read($order, 'user_id'),
            'status' => $this->readValue($this->read($order, 'status')),
            'payment_status' => $this->readValue($this->read($order, 'paymentStatus') ?? $this->read($order, 'payment_status')),
            'currency' => $this->read($order, 'currency'),
            'subtotal' => $this->readMoney($this->read($order, 'subtotal')),
            'shipping_cost' => $this->readMoney($this->read($order, 'shippingCost') ?? $this->read($order, 'shipping_cost')),
            'discount_total' => $this->readMoney($this->read($order, 'discountTotal') ?? $this->read($order, 'discount_total')),
            'tax_total' => $this->readMoney($this->read($order, 'taxTotal') ?? $this->read($order, 'tax_total')),
            'grand_total' => $this->readMoney($this->read($order, 'grandTotal') ?? $this->read($order, 'grand_total')),
            'shipping_address' => $this->readAddress($this->read($order, 'shippingAddress') ?? $this->read($order, 'shipping_address')),
            'payment_method' => $this->read($order, 'paymentMethod') ?? $this->read($order, 'payment_method'),
            'notes' => $this->read($order, 'notes'),
            'items' => OrderItemResource::collection($this->toCollection($this->read($order, 'items'))),
            'status_histories' => OrderStatusHistoryResource::collection(
                $this->toCollection($this->read($order, 'statusHistories') ?? $this->read($order, 'status_histories')),
            ),
            'created_at' => $this->readDate($this->read($order, 'createdAt') ?? $this->read($order, 'created_at')),
            'updated_at' => $this->readDate($this->read($order, 'updatedAt') ?? $this->read($order, 'updated_at')),
        ];
    }

    private function read(object|array|null $source, string $key): mixed
    {
        if ($source === null) {
            return null;
        }

        if (is_array($source)) {
            return $source[$key] ?? null;
        }

        if (method_exists($source, $key)) {
            return $source->{$key}();
        }

        return $source->{$key} ?? null;
    }

    private function readValue(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'value')) {
            return $value->value();
        }

        return $value;
    }

    private function readMoney(mixed $value): float
    {
        if (is_object($value) && method_exists($value, 'amount')) {
            return (float) $value->amount();
        }

        return (float) $value;
    }

    private function readAddress(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return $value;
    }

    private function readDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value) && method_exists($value, 'toISOString')) {
            return $value->toISOString();
        }

        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format(DATE_ATOM);
        }

        return (string) $value;
    }

    private function toCollection(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->all();
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }

        return [];
    }
}