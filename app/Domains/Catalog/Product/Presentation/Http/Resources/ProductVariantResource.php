<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductVariant $variant */
        $variant = $this->resource;

        return [
            'id' => $variant->id(),
            'product_id' => $variant->productId(),

            // TAMBAHAN: Menyertakan store_id sesuai arsitektur database baru
            'store_id' => $variant->storeId(),

            'sku' => $variant->sku(),
            'name' => $variant->name(),
            'price' => $variant->price(),
            'stock' => $variant->stock(),
            'po_stock' => $variant->poStock(),
            'total_stock' => $variant->totalStock(),
            'is_default' => $variant->isDefault(),
            'values' => ProductVariantValueResource::collection($variant->values()),
            'created_at' => $variant->createdAt(),
            'updated_at' => $variant->updatedAt(),
        ];
    }
}
