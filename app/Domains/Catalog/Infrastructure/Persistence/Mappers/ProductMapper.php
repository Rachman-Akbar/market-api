<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Domain\Entities\ProductImage;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;

final class ProductMapper
{
    public static function toEntity(ProductModel $model): Product
    {
        $category = $model->relationLoaded('category') && $model->category
            ? CategoryMapper::toEntity($model->category)
            : null;

        $store = $model->relationLoaded('store') && $model->store
            ? StoreMapper::toEntity($model->store)
            : null;

        $images = $model->relationLoaded('images')
            ? $model->images->map(fn ($img) => new ProductImage(
                id: $img->id,
                productId: $img->product_id,
                imageUrl: $img->image_url,
                isPrimary: (bool) $img->is_primary,
            ))->all()
            : [];

        return new Product(
            id: $model->id,
            storeId: $model->store_id,
            categoryId: $model->category_id,
            sellerId: $model->seller_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            price: (float) $model->price,
            stock: (int) $model->stock,
            thumbnail: $model->thumbnail,
            status: $model->status,
            category: $category,
            store: $store,
            images: $images,
        );
    }

    public static function toModel(Product $product): ProductModel
    {
        return new ProductModel([
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
        ]);
    }
}   