<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Category\Domain\Entities\Category;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;

final class CategoryMapper
{
    public static function toEntity(CategoryModel $model): Category
    {
        $children = [];

        if ($model->relationLoaded('childrenTree')) {
            $children = $model->childrenTree
                ->map(fn (CategoryModel $child) => self::toEntity($child))
                ->all();
        }

        if ($model->relationLoaded('children')) {
            $children = $model->children
                ->map(fn (CategoryModel $child) => self::toEntity($child))
                ->all();
        }

        return new Category(
            id: (int) $model->id,
            catalogGroupId: (int) $model->catalog_group_id,
            parentId: $model->parent_id !== null ? (int) $model->parent_id : null,
            name: (string) $model->name,
            slug: (string) $model->slug,
            fullSlug: (string) $model->full_slug,
            imageUrl: $model->image_url,
            iconUrl: $model->icon_url,
            level: (int) $model->level,
            sortOrder: (int) $model->sort_order,
            productsCount: (int) ($model->getAttribute('products_count') ?? 0),
            isActive: (bool) $model->is_active,
            isVisibleInMenu: (bool) $model->is_visible_in_menu,
            children: $children,
        );
    }
}