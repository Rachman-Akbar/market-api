<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;

final class CatalogGroupMapper
{

public static function toEntity(CatalogGroupModel $model): CatalogGroup
{
    $categories = [];

    if ($model->relationLoaded('categories')) {
        $categories = $model->categories
            ->map(fn ($cat) => CategoryMapper::toEntity($cat))
            ->all();
    }

    return new CatalogGroup(
        id: $model->id,
        name: $model->name,
        slug: $model->slug,
        description: $model->description,
        imageUrl: $model->image_url,
        coverImageUrl: $model->cover_image_url,
        isActive: (bool) $model->is_active,
        categories: $categories
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

public static function toEntityFromArray(array $data): CatalogGroup
{
    $categories = collect($data['categories'] ?? [])
        ->map(fn (array $cat) => CategoryMapper::toEntityFromArray($cat))
        ->all();

    return new CatalogGroup(
        id: $data['id'] ?? null,
        name: $data['name'],
        slug: $data['slug'],
        description: $data['description'] ?? null,
        imageUrl: $data['image_url'] ?? null,
        coverImageUrl: $data['cover_image_url'] ?? null,
        isActive: true,
        categories: $categories
    );
}

}

