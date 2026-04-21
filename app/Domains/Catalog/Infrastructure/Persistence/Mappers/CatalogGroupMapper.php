<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;

class CatalogGroupMapper
{
    public static function toDomain(CatalogGroupModel $model): CatalogGroup
    {
        return new CatalogGroup(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
        );
    }
}