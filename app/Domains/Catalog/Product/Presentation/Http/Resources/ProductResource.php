<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        return [
            'id' => $product->id(),
            'store_id' => $product->storeId(),
            'primary_category_id' => $product->primaryCategoryId(),
            'seller_id' => $product->sellerId(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'description' => $product->description(),
            'brand' => $product->brand(),
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),
            'is_active' => $product->isActive(),
            'category_ids' => $product->categoryIds(),
            'attribute_values' => ProductAttributeValueResource::collection($product->attributeValues()),
            'variants' => ProductVariantResource::collection($product->variants()),
            'created_at' => $product->createdAt(),
            'updated_at' => $product->updatedAt(),
        ];
    }
}
