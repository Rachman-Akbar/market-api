<?php

declare(strict_types=1);

namespace App\Domains\Cart\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CartItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'] ?? null,
            'cart_id' => $this->resource['cart_id'] ?? null,
            'product_id' => $this->resource['product_id'] ?? null,
            'quantity' => $this->resource['quantity'] ?? 0,
            'price_snapshot' => $this->resource['price_snapshot'] ?? 0,
            'subtotal' => $this->resource['subtotal'] ?? 0,
            'product_name_snapshot' => $this->resource['product_name_snapshot'] ?? null,
            'product_image_snapshot' => $this->resource['product_image_snapshot'] ?? null,
        ];
    }
}
