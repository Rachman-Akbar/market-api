<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->resource;

        return [
            'id' => $variant->id(),
            'product_id' => $variant->productId(),
            'sku' => $variant->sku(),
            'name' => $variant->name(),
            'price' => $variant->price(),
            'stock' => $variant->stock(),
            'is_default' => $variant->isDefault(),
            'values' => ProductVariantValueResource::collection($variant->values()),
            'created_at' => $variant->createdAt(),
            'updated_at' => $variant->updatedAt(),
        ];
    }
}
