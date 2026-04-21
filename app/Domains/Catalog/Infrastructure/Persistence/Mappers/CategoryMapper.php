<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use Illuminate\Support\Str;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;

final class CategoryMapper
{
    public static function toEntity(CategoryModel $model): Category
    {
        return new Category(
            $model->id,
            $model->entity_id ?? (string) Str::uuid(),
            $model->name,
            $model->slug,
            $model->description
        );
    }

    public static function toModel(Category $entity): CategoryModel
    {
        return new CategoryModel([
            'id' => $entity->id(),
            'entity_id' => $entity->entityId(),
            'name' => $entity->name(),
            'slug' => $entity->slug(),
            'description' => $entity->description(),
        ]);
    }
}