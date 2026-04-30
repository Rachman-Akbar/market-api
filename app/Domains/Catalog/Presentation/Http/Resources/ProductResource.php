<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use App\Domains\Catalog\Domain\Entities\Product as ProductEntity;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ProductEntity) {
            return $this->fromEntity($this->resource);
        }

        return $this->fromModel();
    }

    private function fromEntity(ProductEntity $product): array
    {
        $category = $product->category();

        return [
            'id' => $product->id(),
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

            'category' => $category ? $this->categoryToArray($category) : null,

            /**
             * Store detail tidak diambil dari Domain Entity.
             * Entity Product cukup menyimpan store_id.
             */
            'store' => null,

            'images' => collect($product->images())
                ->map(fn ($image) => $this->imageToArray($image))
                ->values()
                ->all(),
        ];
    }

    private function fromModel(): array
    {
        return [
            'id' => $this->resource->id,
            'store_id' => $this->resource->store_id,
            'category_id' => $this->resource->category_id,
            'seller_id' => $this->resource->seller_id,

            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'price' => (float) $this->resource->price,
            'stock' => (int) $this->resource->stock,
            'thumbnail' => $this->resource->thumbnail,
            'status' => $this->resource->status,

            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->resource->category?->id,
                'name' => $this->resource->category?->name,
                'slug' => $this->resource->category?->slug,
            ]),

            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->resource->store?->id,
                'name' => $this->resource->store?->name,
                'slug' => $this->resource->store?->slug,
                'logo_url' => $this->resource->store?->logo_url
                    ?? $this->resource->store?->logo
                    ?? null,
            ]),

            'images' => $this->whenLoaded('images', fn () => $this->resource->images
                ->map(fn ($image) => $this->imageToArray($image))
                ->values()
                ->all()
            ),
        ];
    }

    private function categoryToArray(mixed $category): array
    {
        return [
            'id' => $this->read($category, 'id'),
            'name' => $this->read($category, 'name'),
            'slug' => $this->read($category, 'slug'),
        ];
    }

    private function imageToArray(mixed $image): array
    {
        return [
            'id' => $this->read($image, 'id'),
            'image_url' => $this->read($image, 'image_url')
                ?? $this->read($image, 'imageUrl')
                ?? $this->read($image, 'url'),
            'url' => $this->read($image, 'url')
                ?? $this->read($image, 'image_url')
                ?? $this->read($image, 'imageUrl'),
            'is_primary' => (bool) (
                $this->read($image, 'is_primary')
                ?? $this->read($image, 'isPrimary')
                ?? false
            ),
        ];
    }

    private function read(mixed $source, string $key, mixed $default = null): mixed
    {
        if ($source === null) {
            return $default;
        }

        if (is_array($source)) {
            return data_get($source, $key, $default);
        }

        if (! is_object($source)) {
            return $default;
        }

        if (method_exists($source, $key)) {
            return $source->{$key}();
        }

        $camelKey = Str::camel($key);

        if (method_exists($source, $camelKey)) {
            return $source->{$camelKey}();
        }

        return data_get($source, $key, $default);
    }
}
