<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CatalogGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'slug' => $this->slug(),
            'description' => $this->description(),
            'image_url' => method_exists($this->resource, 'imageUrl') ? $this->imageUrl() : null,
            'cover_image_url' => method_exists($this->resource, 'coverImageUrl') ? $this->coverImageUrl() : null,
            'categories' => collect(method_exists($this->resource, 'categories') ? $this->categories() : [])
                ->map(fn ($category) => [
                    'id' => $category->id(),
                    'name' => $category->name(),
                    'slug' => $category->slug(),
                    'description' => method_exists($category, 'description') ? $category->description() : null,
                    'image_url' => method_exists($category, 'imageUrl') ? $category->imageUrl() : null,
                    'cover_image_url' => method_exists($category, 'coverImageUrl') ? $category->coverImageUrl() : null,
                    'products_count' => method_exists($category, 'productsCount') ? $category->productsCount() : null,
                ])
                ->values(),
        ];
    }
}