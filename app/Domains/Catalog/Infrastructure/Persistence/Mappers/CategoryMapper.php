<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;

final class CategoryMapper
{
    public static function toEntity(CategoryModel $model): Category
    {
        $children = [];

        if ($model->relationLoaded('childrenRecursive') || $model->relationLoaded('children')) {
            $relation = $model->relationLoaded('childrenRecursive') 
                ? $model->childrenRecursive 
                : $model->children;

            $children = $relation->map(fn (CategoryModel $child) => self::toEntity($child))->all();
        }

        return new Category(
            id: $model->id,
            catalogGroupId: $model->catalog_group_id,
            parentId: $model->parent_id,
            name: $model->name,
            slug: $model->slug,
            fullSlug: $model->full_slug,
            description: $model->description,
            imageUrl: $model->image_url,
            iconUrl: $model->icon_url,
            coverImageUrl: $model->cover_image_url,
            level: (int) ($model->level ?? 1),
            sortOrder: (int) ($model->sort_order ?? 0),
            productsCount: $model->products_count ?? null,
            isActive: (bool) ($model->is_active ?? true),
            isVisibleInMenu: (bool) ($model->is_visible_in_menu ?? true),
            children: $children
        );
    }

    /**
     * Untuk keperluan Caching (Array → Entity)
     */
    public static function toEntityFromArray(array $data): Category
    {
        $children = collect($data['children'] ?? [])
            ->map(fn (array $child) => self::toEntityFromArray($child))
            ->all();

        return new Category(
            id: $data['id'] ?? null,
            catalogGroupId: $data['catalog_group_id'] ?? null,
            parentId: $data['parent_id'] ?? null,
            name: $data['name'],
            slug: $data['slug'],
            fullSlug: $data['full_slug'] ?? null,
            description: $data['description'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            iconUrl: $data['icon_url'] ?? null,
            coverImageUrl: $data['cover_image_url'] ?? null,
            level: (int) ($data['level'] ?? 1),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            productsCount: $data['products_count'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            isVisibleInMenu: (bool) ($data['is_visible_in_menu'] ?? true),
            children: $children
        );
    }

    public static function toModel(Category $category): CategoryModel
    {
        return new CategoryModel([
            'catalog_group_id'   => $category->catalogGroupId(),
            'parent_id'          => $category->parentId(),
            'name'               => $category->name(),
            'slug'               => $category->slug(),
            'full_slug'          => $category->fullSlug(),
            'description'        => $category->description(),
            'image_url'          => $category->imageUrl(),
            'icon_url'           => $category->iconUrl(),
            'cover_image_url'    => $category->coverImageUrl(),
            'level'              => $category->level(),
            'sort_order'         => $category->sortOrder(),
            'is_active'          => $category->isActive(),
            'is_visible_in_menu' => $category->isVisibleInMenu(),
        ]);
    }
}


