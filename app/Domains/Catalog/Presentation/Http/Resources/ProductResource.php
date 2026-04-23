<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $category = method_exists($this->resource, 'category') ? $this->category() : null;
        $store = method_exists($this->resource, 'store') ? $this->store() : null;
        $images = method_exists($this->resource, 'images') ? $this->images() : [];

        return [
            'id' => $this->resource->id(),
            'seller_id' => $this->resource->sellerId(),
            'store_id' => method_exists($this->resource, 'storeId') ? $this->resource->storeId() : null,
            'category_id' => method_exists($this->resource, 'categoryId') ? $this->resource->categoryId() : null,
            'name' => $this->resource->name(),
            'slug' => $this->resource->slug(),
            'description' => $this->resource->description(),
            'price' => $this->resource->price(),
            'stock' => method_exists($this->resource, 'stock') ? $this->resource->stock() : null,
            'thumbnail' => method_exists($this->resource, 'thumbnail') ? $this->resource->thumbnail() : null,
            'status' => $this->resource->status(),
            'category' => $category ? [
                'id' => $category->id(),
                'name' => $category->name(),
                'slug' => $category->slug(),
                'image_url' => method_exists($category, 'imageUrl') ? $category->imageUrl() : null,
            ] : null,
            'store' => $store ? [
                'id' => $store->id(),
                'name' => $store->name(),
                'slug' => $store->slug(),
                'logo_url' => method_exists($store, 'logoUrl') ? $store->logoUrl() : null,
            ] : null,
            'images' => collect($images)->map(fn ($image) => [
                'id' => method_exists($image, 'id') ? $image->id() : null,
                'image_url' => method_exists($image, 'imageUrl') ? $image->imageUrl() : null,
                'is_primary' => method_exists($image, 'isPrimary') ? $image->isPrimary() : false,
            ])->values(),
        ];
    }
}