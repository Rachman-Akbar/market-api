<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'products_count' => method_exists($this->resource, 'productsCount') ? $this->productsCount() : null,
            'catalog_group_id' => method_exists($this->resource, 'catalogGroupId') ? $this->catalogGroupId() : null,
        ];
    }
}