<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Mappers\ProductVariantMapper;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Mappers\CategoryMapper;

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

        // Ditambahkan: Map relasi varian
        $variants = [];
        if ($model->relationLoaded('variants')) {
            $variants = $model->variants
                ->map(fn ($variantModel) => ProductVariantMapper::toEntity($variantModel))
                ->all();
        }

        return new Product(
            id: $model->id ? (int) $model->id : null,
            storeId: $model->store_id ? (int) $model->store_id : null,
            primaryCategoryId: $model->primary_category_id ? (int) $model->primary_category_id : null,
            categoryIds: $categoryIds,
            sellerId: (string) $model->seller_id,
            name: (string) $model->name,
            slug: (string) $model->slug,
            description: $model->description,
            thumbnail: $model->thumbnail,
            status: (string) $model->status,
            primaryCategory: $primaryCategory,
            categories: $categories,
            images: $model->relationLoaded('images') ? $model->images->all() : [],
            variants: $variants // Set data varian ke dalam entitas
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
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),
            // Kolom price & stock dibersihkan dari pemetaan model utama
        ]);
    }
}
