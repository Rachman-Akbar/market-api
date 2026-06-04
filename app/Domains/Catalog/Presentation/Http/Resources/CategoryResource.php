<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this->resource adalah objek Domain Entity Category
        return [
            'id'                 => $this->resource->id(),
            'catalog_group_id'   => $this->resource->catalogGroupId(),
            'parent_id'          => $this->resource->parentId(),
            'name'               => $this->resource->name(),
            'slug'               => $this->resource->slug(),
            'full_slug'          => $this->resource->fullSlug(),
            'image_url'          => $this->resource->imageUrl(),
            'icon_url'           => $this->resource->iconUrl(),
            'level'              => $this->resource->level(),
            'sort_order'         => $this->resource->sortOrder(),
            'products_count'     => $this->resource->productsCount(),
            'is_active'          => $this->resource->isActive(),
            'is_visible_in_menu' => $this->resource->isVisibleInMenu(),
            // Recursive loading untuk children tree
            'children'           => CategoryResource::collection($this->resource->children()),
        ];
    }
}