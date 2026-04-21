<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;

class CategoryMapper
{
    /**
     * Model → Entity
     */
    public static function toEntity(CategoryModel $model): Category
    {
        return new Category(
            id: $model->id,
            entityId: $model->entity_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description
        );
    }

    /**
     * Entity → Array (for database)
     */
    public static function toPersistence(Category $category): array
    {
        return [
            'entity_id' => $category->entityId(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'description' => $category->description(),
        ];
    }
}
