<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Domain\Entities\Category;

final class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Category $category */
        $category = $this->resource;

        return [
            'id'                => $category->id(),
            'catalog_group_id'  => $category->catalogGroupId(),
            'parent_id'         => $category->parentId(),
            'name'              => $category->name(),
            'slug'              => $category->slug(),
            'full_slug'         => $category->fullSlug(),
            'image_url'         => $category->imageUrl(),
            'icon_url'          => $category->iconUrl(),
            'level'             => $category->level(),
            'sort_order'        => $category->sortOrder(),
            'products_count'    => $category->productsCount(),
            'is_active'         => $category->isActive(),
            'is_visible_in_menu'=> $category->isVisibleInMenu(),
            'children'          => CategoryResource::collection($category->children()),
        ];
    }
}
