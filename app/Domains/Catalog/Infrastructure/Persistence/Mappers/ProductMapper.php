<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Product;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel;

final class ProductMapper
{
    public static function toEntity(ProductModel $model): Product
    {
        $primaryCategory = null;

        if ($model->relationLoaded('primaryCategory') && $model->primaryCategory) {
            $primaryCategory = CategoryMapper::toEntity($model->primaryCategory);
        }

        $categories = [];
        $categoryIds = [];

        if ($model->relationLoaded('categories')) {
            $categories = $model->categories
                ->map(fn ($category) => CategoryMapper::toEntity($category))
                ->values()
                ->all();

            $categoryIds = $model->categories
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if (empty($categoryIds) && $model->primary_category_id) {
            $categoryIds = [(int) $model->primary_category_id];
        }

        return new Product(
            id: $model->id,
            storeId: $model->store_id,
            primaryCategoryId: $model->primary_category_id,
            categoryIds: $categoryIds,
            sellerId: (string) $model->seller_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            price: (float) $model->price,
            stock: (int) $model->stock,
            thumbnail: $model->thumbnail,
            status: $model->status,
            primaryCategory: $primaryCategory,
            categories: $categories,
            images: $model->relationLoaded('images')
                ? $model->images->all()
                : [],
        );
    }

    public static function toModel(Product $product): ProductModel
    {
        return new ProductModel([
            'store_id' => $product->storeId(),
            'primary_category_id' => $product->primaryCategoryId(),
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
