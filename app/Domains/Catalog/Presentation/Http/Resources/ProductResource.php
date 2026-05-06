<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Entities\Product;

final class ProductResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return [
            'id' => $product->id(),
            'store_id' => $product->storeId(),

            /**
             * Field baru.
             */
            'primary_category_id' => $product->primaryCategoryId(),
            'category_ids' => $product->categoryIds(),

            /**
             * Field lama untuk sementara.
             * Boleh kamu hapus nanti setelah frontend selesai migrasi.
             */
            'category_id' => $product->primaryCategoryId(),

            'seller_id' => $product->sellerId(),

            'name' => $product->name(),
            'slug' => $product->slug(),
            'description' => $product->description(),

            'price' => $product->price(),
            'stock' => $product->stock(),
            'thumbnail' => $product->thumbnail(),
            'status' => $product->status(),

            /**
             * Field baru.
             */
            'primary_category' => $this->mapCategory($product->primaryCategory()),
            'categories' => $this->mapCategories($product->categories()),

            /**
             * Field lama untuk sementara.
             * Isinya sama dengan primary_category.
             */
            'category' => $this->mapCategory($product->primaryCategory()),

            'images' => $this->mapImages($product->images()),
        ];
    }

    private function mapCategory(?Category $category): ?array
    {
        if (! $category) {
            return null;
        }

        return [
            'id' => $category->id(),
            'catalog_group_id' => $category->catalogGroupId(),
            'parent_id' => $category->parentId(),

            'name' => $category->name(),
            'slug' => $category->slug(),
            'full_slug' => $category->fullSlug(),

            'image_url' => $category->imageUrl(),
            'icon_url' => $category->iconUrl(),

            'level' => $category->level(),
            'sort_order' => $category->sortOrder(),

            'is_active' => $category->isActive(),
            'is_visible_in_menu' => $category->isVisibleInMenu(),
        ];
    }

    /**
     * @param array<int, Category> $categories
     * @return array<int, array<string, mixed>>
     */
    private function mapCategories(array $categories): array
    {
        return array_values(array_filter(array_map(
            fn (Category $category): ?array => $this->mapCategory($category),
            $categories
        )));
    }

    /**
     * @param array<int, mixed> $images
     * @return array<int, array<string, mixed>>
     */
    private function mapImages(array $images): array
    {
        return array_map(function (mixed $image): array {
            if (is_array($image)) {
                return [
                    'id' => $image['id'] ?? null,
                    'image_url' => $image['image_url'] ?? $image['url'] ?? null,
                    'url' => $image['url'] ?? $image['image_url'] ?? null,
                    'is_primary' => (bool) ($image['is_primary'] ?? false),
                ];
            }

            return [
                'id' => $image->id ?? null,
                'image_url' => $image->image_url ?? $image->url ?? null,
                'url' => $image->url ?? $image->image_url ?? null,
                'is_primary' => (bool) ($image->is_primary ?? false),
            ];
        }, $images);
    }
}
