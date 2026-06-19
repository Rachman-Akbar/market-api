<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\UseCases\Product;

use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductAttributeValueRepositoryInterface;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductAttributeValueRepositoryInterface $attributeValues
    ) {}

    public function execute(int|string $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data) {
            $current = $this->products->findById((int) $id);

            abort_if(! $current, 404, 'Product not found.');

            $product = $this->products->save(new Product(
                id: $current->id(),
                storeId: (int) ($data['store_id'] ?? $current->storeId()),
                primaryCategoryId: array_key_exists('primary_category_id', $data) ? ($data['primary_category_id'] ? (int) $data['primary_category_id'] : null) : $current->primaryCategoryId(),
                sellerId: $current->sellerId(),
                name: (string) ($data['name'] ?? $current->name()),
                slug: (string) ($data['slug'] ?? $current->slug()),
                description: array_key_exists('description', $data) ? $data['description'] : $current->description(),
                brand: array_key_exists('brand', $data) ? $data['brand'] : $current->brand(),
                thumbnail: array_key_exists('thumbnail', $data) ? $data['thumbnail'] : $current->thumbnail(),
                status: (string) ($data['status'] ?? $current->status()),
                isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $current->isActive(),
                categoryIds: array_key_exists('category_ids', $data) ? array_map('intval', $data['category_ids'] ?? []) : $current->categoryIds()
            ));

            if (array_key_exists('attribute_values', $data)) {
                $this->attributeValues->replaceForProduct(
                    productId: (int) $product->id(),
                    values: $this->normalizeAttributeValues($data['attribute_values'] ?? [])
                );
            }

            return $this->products->findById((int) $product->id());
        });
    }

    private function normalizeAttributeValues(array $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            if (! isset($item['attribute_id'])) {
                continue;
            }

            if (isset($item['values']) && is_array($item['values'])) {
                foreach ($item['values'] as $value) {
                    $rows[] = [
                        'attribute_id' => (int) $item['attribute_id'],
                        'value' => (string) $value,
                    ];
                }

                continue;
            }

            if (array_key_exists('value', $item)) {
                $rows[] = [
                    'attribute_id' => (int) $item['attribute_id'],
                    'value' => (string) $item['value'],
                ];
            }
        }

        return $rows;
    }
}
