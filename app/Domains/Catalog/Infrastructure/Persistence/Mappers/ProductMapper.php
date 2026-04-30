<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;

final class ProductMapper
{
    public static function toEntity(ProductModel $model): Product
    {
        return new Product(
            id: $model->id,
            storeId: $model->store_id,
            categoryId: $model->category_id,
            sellerId: (string) $model->seller_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            price: (float) $model->price,
            stock: (int) $model->stock,
            thumbnail: $model->thumbnail,
            status: $model->status,
            category: $model->relationLoaded('category') && $model->category
                ? CategoryMapper::toEntity($model->category)
                : null,
            images: $model->relationLoaded('images')
                ? $model->images->all()
                : [],
        );
    }

    public static function toModel(Product $product): array
    {
        return [
            'store_id' => $product->storeId(),
            'category_id' => $product->categoryId(),
            'seller_id' => $product->sellerId(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'description' => $product->description(),
            'price' => $product->price(),
            'stock' => $product->stock(),
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),
        ];
    }
}
