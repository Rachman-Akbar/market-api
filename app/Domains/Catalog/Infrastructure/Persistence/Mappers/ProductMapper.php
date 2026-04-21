<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;

class ProductMapper
{
    public static function toEntity(ProductModel $model): Product
    {
        return Product::rehydrate(
            id: (string) $model->id,
            sellerId: $model->seller_id,
            name: $model->name,
            slug: $model->slug,
            price: (int) $model->price,
            status: $model->status,
            description: $model->description,
        );
    }
}
