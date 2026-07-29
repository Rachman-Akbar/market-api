<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->resource;
        $variants = $product->variants();
        $targetVariant = null;

        foreach ($variants as $variant) {
            if ($variant instanceof ProductVariant && $variant->isDefault()) {
                $targetVariant = $variant;
                break;
            }
        }

        if ($targetVariant === null && $variants !== []) {
            $targetVariant = $variants[0];
        }

        $store = $product->store();

        return [
            'id' => $product->id(),
            'store_id' => $product->storeId(),
            'store' => $store !== [] ? $store : null,
            'store_name' => $store['name'] ?? null,
            'store_slug' => $store['slug'] ?? null,
            'store_logo' => $store['logo'] ?? null,
            'store_city' => $store['city'] ?? null,
            'store_province' => $store['province'] ?? null,
            'primary_category_id' => $product->primaryCategoryId(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'sku' => $targetVariant ? $targetVariant->sku() : null,
            'price' => $targetVariant ? $targetVariant->price() : 0.0,
            'stock' => $targetVariant ? $targetVariant->stock() : 0,
            'description' => $product->description(),
            'brand' => $product->brand(),
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),
            'is_active' => $product->isActive(),
            'category_ids' => $product->categoryIds(),
            'images' => ProductImageResource::collection($product->images()),
            'attribute_values' => ProductAttributeValueResource::collection($product->attributeValues()),
            'variants' => ProductVariantResource::collection($product->variants()),
            'created_at' => $product->createdAt(),
            'updated_at' => $product->updatedAt(),
        ];
    }
}
