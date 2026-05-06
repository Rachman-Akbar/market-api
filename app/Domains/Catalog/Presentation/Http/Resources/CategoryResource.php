<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Domain\Entities\Category;

final class CategoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Category $category */
        $category = $this->resource;

        return [
            'id' => $category->id(),
            'catalog_group_id' => $category->catalogGroupId(),
            'parent_id' => $category->parentId(),

            'name' => $category->name(),
            'slug' => $category->slug(),
            'full_slug' => $category->fullSlug(),
            'description' => $category->description(),

            'image_url' => $category->imageUrl(),
            'icon_url' => $category->iconUrl(),
            'cover_image_url' => $category->coverImageUrl(),

            'level' => $category->level(),
            'sort_order' => $category->sortOrder(),

            'products_count' => $category->productsCount(),

            'is_active' => $category->isActive(),
            'is_visible_in_menu' => $category->isVisibleInMenu(),

            'children' => $this->mapChildren($category->children()),
        ];
    }

    /**
     * @param array<int, Category> $children
     * @return array<int, array<string, mixed>>
     */
    private function mapChildren(array $children): array
    {
        return array_map(function (Category $child): array {
            return [
                'id' => $child->id(),
                'catalog_group_id' => $child->catalogGroupId(),
                'parent_id' => $child->parentId(),

                'name' => $child->name(),
                'slug' => $child->slug(),
                'full_slug' => $child->fullSlug(),
                'description' => $child->description(),

                'image_url' => $child->imageUrl(),
                'icon_url' => $child->iconUrl(),
                'cover_image_url' => $child->coverImageUrl(),

                'level' => $child->level(),
                'sort_order' => $child->sortOrder(),

                'products_count' => $child->productsCount(),

                'is_active' => $child->isActive(),
                'is_visible_in_menu' => $child->isVisibleInMenu(),

                'children' => $this->mapChildren($child->children()),
            ];
        }, $children);
    }
}
