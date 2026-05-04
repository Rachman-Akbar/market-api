<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->resource;

        return [
            'id' => $this->read($item, 'id'),
            'product_id' => $this->read($item, 'productId') ?? $this->read($item, 'product_id'),
            'product_name' => $this->read($item, 'productName') ?? $this->read($item, 'product_name'),
            'sku' => $this->read($item, 'sku'),
            'quantity' => (int) $this->read($item, 'quantity'),
            'currency' => $this->read($item, 'currency'),
            'unit_price' => $this->readMoney($this->read($item, 'unitPrice') ?? $this->read($item, 'unit_price')),
            'subtotal' => $this->readMoney($this->read($item, 'subtotal')),
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

    private function readMoney(mixed $value): float
    {
        if (is_object($value) && method_exists($value, 'amount')) {
            return (float) $value->amount();
        }

        return (float) $value;
    }
}