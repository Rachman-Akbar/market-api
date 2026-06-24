<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;

final class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Domains\Catalog\Product\Domain\Entities\Product $product */
        $product = $this->resource;

        // Ambil semua array varian dari entitas Product
        $variants = $product->variants();

        // 1. Cari varian yang ditandai sebagai default
        $targetVariant = null;
        foreach ($variants as $variant) {
            if ($variant instanceof ProductVariant && $variant->isDefault()) {
                $targetVariant = $variant;
                break;
            }
        }

        // 2. Fallback: Jika tidak ada varian default, gunakan varian pertama di dalam array
        if ($targetVariant === null && !empty($variants)) {
            $targetVariant = $variants[0];
        }

        return [
            'id' => $product->id(),
            'store_id' => $product->storeId(),
            'primary_category_id' => $product->primaryCategoryId(),
            'seller_id' => $product->sellerId(),
            'name' => $product->name(),
            'slug' => $product->slug(),

            // --- Data Ringkasan (Diambil dari Varian Terpilih) ---
            'sku' => $targetVariant ? $targetVariant->sku() : null,
            'price' => $targetVariant ? $targetVariant->price() : 0.0,
            'stock' => $targetVariant ? $targetVariant->stock() : 0,
            // -----------------------------------------------------

            'description' => $product->description(),
            'brand' => $product->brand(),
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),
            'is_active' => $product->isActive(),
            'category_ids' => $product->categoryIds(),

            // Data Koleksi Relasi Domain
            'images' => ProductImageResource::collection($product->images()),
            'attribute_values' => ProductAttributeValueResource::collection($product->attributeValues()),
            'variants' => ProductVariantResource::collection($product->variants()),
            'created_at' => $product->createdAt(),
            'updated_at' => $product->updatedAt(),
        ];
    }
}
