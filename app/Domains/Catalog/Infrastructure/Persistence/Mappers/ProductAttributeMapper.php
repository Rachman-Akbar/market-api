<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\ProductAttribute;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductAttributeModel;

final class ProductAttributeMapper
{
    public static function toEntity(
        ProductAttributeModel $model
    ): ProductAttribute {

        return new ProductAttribute(
            id: (int)$model->id,
            name: (string)$model->name,
            slug: (string)$model->slug,
            type: (string)$model->type,
            isActive: (bool)$model->is_active,
        );
    }
}
