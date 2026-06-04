<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductVariantModel;

final class ProductVariantMapper
{
    public static function toEntity(
        ProductVariantModel $model
    ): ProductVariant {

        return new ProductVariant(
            id: (int) $model->id,
            productId: (int) $model->product_id,
            sku: $model->sku,
            name: $model->name,
            price: (float) $model->price,
            stock: (int) $model->stock,
            isDefault: (bool) $model->is_default,
            values: $model->relationLoaded('values')
                ? $model->values->toArray()
                : [],
        );
    }
}