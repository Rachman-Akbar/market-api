<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;

final class CategoryMapper
{
    public static function toEntity(CategoryModel $model): Category
    {
        return new Category(
            id: $model->id,
            catalogGroupId: $model->catalog_group_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            imageUrl: $model->image_url,
            coverImageUrl: $model->cover_image_url,
            productsCount: $model->products_count ?? null,
            isActive: (bool) ($model->is_active ?? true),
        );
    }

    public static function toModel(Category $category): CategoryModel
    {
        return new CategoryModel([
            'catalog_group_id' => $category->catalogGroupId(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'description' => $category->description(),
            'image_url' => $category->imageUrl(),
            'cover_image_url' => $category->coverImageUrl(),
            'is_active' => $category->isActive(),
        ]);
    }
}