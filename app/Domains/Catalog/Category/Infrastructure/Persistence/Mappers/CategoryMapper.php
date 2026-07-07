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

    public static function toEntityFromArray(array|object $data): Category
    {
        $data = (array) $data;

        // Map children secara rekursif terlebih dahulu
        $children = collect($data['children'] ?? $data['children_tree'] ?? [])
            ->map(fn ($child) => self::toEntityFromArray((array) $child))
            ->all();

        // Urutan WAJIB sama persis dengan __construct di file Category.php kamu:
        // 1. id, 2. catalogGroupId, 3. parentId, 4. name, 5. slug, 6. fullSlug,
        // 7. imageUrl, 8. iconUrl, 9. level, 10. sortOrder, 11. productsCount,
        // 12. isActive, 13. isVisibleInMenu, 14. children
        return new Category(
            id: isset($data['id']) ? (int) $data['id'] : null,
            catalogGroupId: (int) ($data['catalog_group_id'] ?? 0),
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            name: (string) ($data['name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            fullSlug: (string) ($data['full_slug'] ?? ''),
            imageUrl: $data['image_url'] ?? null,
            iconUrl: $data['icon_url'] ?? null,
            level: (int) ($data['level'] ?? 1),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            productsCount: (int) ($data['products_count'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
            isVisibleInMenu: (bool) ($data['is_visible_in_menu'] ?? true),
            children: $children,
        );
    }
}
