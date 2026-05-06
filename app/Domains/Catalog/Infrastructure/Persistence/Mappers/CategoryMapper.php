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

        if ($model->relationLoaded('childrenRecursive')) {
            $children = $model->childrenRecursive
                ->map(fn (CategoryModel $child): Category => self::toEntity($child))
                ->values()
                ->all();
        } elseif ($model->relationLoaded('children')) {
            $children = $model->children
                ->map(fn (CategoryModel $child): Category => self::toEntity($child))
                ->values()
                ->all();
        }

        return new Category(
            id: $model->id ? (int) $model->id : null,
            catalogGroupId: $model->catalog_group_id ? (int) $model->catalog_group_id : null,
            parentId: $model->parent_id ? (int) $model->parent_id : null,
            name: (string) $model->name,
            slug: (string) $model->slug,
            fullSlug: $model->full_slug,
            description: $model->description,
            imageUrl: $model->image_url,
            iconUrl: $model->icon_url,
            coverImageUrl: $model->cover_image_url,
            level: (int) ($model->level ?? 1),
            sortOrder: (int) ($model->sort_order ?? 0),
            productsCount: isset($model->products_count)
                ? (int) $model->products_count
                : null,
            isActive: (bool) ($model->is_active ?? true),
            isVisibleInMenu: (bool) ($model->is_visible_in_menu ?? true),
            children: $children,
        );
    }

    public static function toModel(Category $category): CategoryModel
    {
        return new CategoryModel([
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
            'is_active' => $category->isActive(),
            'is_visible_in_menu' => $category->isVisibleInMenu(),
        ]);
    }
}
