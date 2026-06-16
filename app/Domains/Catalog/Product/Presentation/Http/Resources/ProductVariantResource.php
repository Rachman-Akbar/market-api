<?php

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ProductVariantResource
extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id(),
            'product_id' => $this->productId(),
            'sku' => $this->sku(),
            'name' => $this->name(),
            'price' => $this->price(),
            'stock' => $this->stock(),
            'is_default' => $this->isDefault(),
            'values' => $this->values(),
        ];
    }
}