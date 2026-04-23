<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;

final class CatalogGroupMapper
{
    public static function toEntity(CatalogGroupModel $model): CatalogGroup
    {
        return new CatalogGroup(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            imageUrl: $model->image_url,
            coverImageUrl: $model->cover_image_url,
            isActive: (bool) ($model->is_active ?? true),
            categories: $model->relationLoaded('categories')
                ? $model->categories->map(fn ($item) => CategoryMapper::toEntity($item))->all()
                : [],
        );
    }

    public static function toModel(CatalogGroup $group): CatalogGroupModel
    {
        return new CatalogGroupModel([
            'name' => $group->name(),
            'slug' => $group->slug(),
            'description' => $group->description(),
            'image_url' => $group->imageUrl(),
            'cover_image_url' => $group->coverImageUrl(),
            'is_active' => $group->isActive(),
        ]);
    }
}