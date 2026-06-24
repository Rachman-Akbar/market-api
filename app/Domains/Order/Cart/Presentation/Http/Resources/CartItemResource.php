<?php

declare(strict_types=1);

namespace App\Domains\Order\Cart\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'variant_id'  => $this->resource['variant_id'],
            'name'        => $this->resource['name'],
            'sku'         => $this->resource['sku'],
            'price'       => $this->resource['price'],
            'quantity'    => $this->resource['quantity'],
            'subtotal'    => $this->resource['subtotal'],
            'attributes'  => $this->resource['attributes'] ?? [], // Array key-value e.g., {"Ukuran": "XL", "Warna": "Putih"}
        ];
    }
}