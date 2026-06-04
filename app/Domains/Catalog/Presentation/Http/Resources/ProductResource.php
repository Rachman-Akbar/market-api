<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Entities\Product;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return [
            'id' => $product->id(),
            'store_id' => $product->storeId(),

            'primary_category_id' => $product->primaryCategoryId(),
            'category_ids' => $product->categoryIds(),

            'seller_id' => $product->sellerId(),

            'name' => $product->name(),
            'slug' => $product->slug(),
            'description' => $product->description(),

            'price' => $product->price(),
            'stock' => $product->stock(),
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),

            'primary_category' => $this->mapCategory($product->primaryCategory()),
            'categories' => $this->mapCategories($product->categories()),

            // ➕ TAMBAHKAN: Pemetaan Varian Produk
            // Menggunakan method check method_exists atau directly call jika method pasti ada di Entity
            'variants' => method_exists($product, 'variants') ? $this->mapVariants($product->variants()) : [],

            /**
             * Legacy response supaya frontend lama tidak langsung rusak.
             */
            'category_id' => $product->primaryCategoryId(),
            'category' => $this->mapCategory($product->primaryCategory()),

            'images' => $this->mapImages($product->images()),
        ];
    }

    private function mapCategory(?Category $category): ?array
    {
        if (! $category) {
            return null;
        }

        return [
            'id' => $category->id(),
            'catalog_group_id' => $category->catalogGroupId(),
            'parent_id' => $category->parentId(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'full_slug' => $category->fullSlug(),
            'image_url' => $category->imageUrl(),
            'icon_url' => $category->iconUrl(),
            'level' => $category->level(),
            'sort_order' => $category->sortOrder(),
            'is_active' => $category->isActive(),
            'is_visible_in_menu' => $category->isVisibleInMenu(),
        ];
    }

    private function mapCategories(array $categories): array
    {
        return array_values(array_filter(array_map(
            fn (Category $category): ?array => $this->mapCategory($category),
            $categories
        )));
    }

    private function mapImages(array $images): array
    {
        return array_map(function (mixed $image): array {
            if (is_array($image)) {
                return [
                    'id' => $image['id'] ?? null,
                    'image_url' => $image['image_url'] ?? $image['url'] ?? null,
                    'url' => $image['url'] ?? $image['image_url'] ?? null,
                    'is_primary' => (bool) ($image['is_primary'] ?? false),
                ];
            }

            return [
                'id' => $image->id ?? null,
                'image_url' => $image->image_url ?? $image->url ?? null,
                'url' => $image->url ?? $image->image_url ?? null,
                'is_primary' => (bool) ($image->is_primary ?? false),
            ];
        }, $images);
    }

    /**
     * ➕ TAMBAHKAN: Helper method untuk mapping array Variant Entity / Array data variant
     */
    private function mapVariants(?array $variants): array
    {
        if (empty($variants)) {
            return [];
        }

        return array_map(function (mixed $variant): array {
            // Jika Variant bertipe Object (Domain Entity Variant)
            if (is_object($variant)) {
                return [
                    'id'         => method_exists($variant, 'id') ? $variant->id() : ($variant->id ?? null),
                    'name'       => method_exists($variant, 'name') ? $variant->name() : ($variant->name ?? null),
                    'sku'        => method_exists($variant, 'sku') ? $variant->sku() : ($variant->sku ?? null),
                    'price'      => method_exists($variant, 'price') ? $variant->price() : ($variant->price ?? null),
                    'stock'      => method_exists($variant, 'stock') ? $variant->stock() : ($variant->stock ?? null),
                    'options'    => method_exists($variant, 'options') ? $variant->options() : ($variant->options ?? []), // e.g., ['color' => 'red', 'size' => 'L']
                ];
            }

            // Jika Variant bertipe Array murni
            if (is_array($variant)) {
                return [
                    'id'         => $variant['id'] ?? null,
                    'name'       => $variant['name'] ?? null,
                    'sku'        => $variant['sku'] ?? null,
                    'price'      => $variant['price'] ?? null,
                    'stock'      => $variant['stock'] ?? null,
                    'options'    => $variant['options'] ?? [], 
                ];
            }

            return [];
        }, $variants);
    }
}