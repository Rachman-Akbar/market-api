<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductVariantModel;

final class ProductVariantMapper
{
    public static function toEntity(ProductVariantModel $model): ProductVariant
    {
        $values = $model->relationLoaded('values')
            ? $model->values->map(fn ($item) => ProductVariantValueMapper::toEntity($item))->all()
            : [];

        return new ProductVariant(
            id: (int) $model->id,
            productId: (int) $model->product_id,
            sku: (string) $model->sku,
            name: (string) $model->name,
            price: (float) $model->price,
            stock: (int) $model->stock,
            isDefault: (bool) $model->is_default,
            values: $values,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }
}


