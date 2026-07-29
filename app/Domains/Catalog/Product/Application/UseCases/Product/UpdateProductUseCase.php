<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\UseCases\Product;

use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Product\Domain\Repositories\ProductAttributeValueRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductImageRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $variants,
        private readonly ProductAttributeValueRepositoryInterface $attributeValues,
        private readonly ProductImageRepositoryInterface $productImages
    ) {}

    public function execute(int|string $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data): Product {
            $current = $this->products->findById((int) $id, true);

            abort_if(! $current, 404, 'Product not found.');

            $storeId = (int) ($data['store_id'] ?? $current->storeId());
            $name = Str::lower(trim((string) ($data['name'] ?? $current->name())));

            if ($this->products->nameExistsForStore($name, $storeId, (int) $current->id())) {
                throw new InvalidArgumentException("Nama produk '{$name}' sudah digunakan pada toko ini.");
            }

            $product = $this->products->save(new Product(
                id: $current->id(),
                storeId: $storeId,
                primaryCategoryId: array_key_exists('primary_category_id', $data)
                    ? ($data['primary_category_id'] ? (int) $data['primary_category_id'] : null)
                    : $current->primaryCategoryId(),
                name: $name,
                slug: (string) ($data['slug'] ?? $current->slug()),
                description: array_key_exists('description', $data) ? $data['description'] : $current->description(),
                brand: array_key_exists('brand', $data) ? $data['brand'] : $current->brand(),
                thumbnail: array_key_exists('thumbnail', $data) ? $data['thumbnail'] : $current->thumbnail(),
                status: (string) ($data['status'] ?? $current->status()),
                isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $current->isActive(),
                categoryIds: array_key_exists('category_ids', $data)
                    ? array_map('intval', $data['category_ids'] ?? [])
                    : $current->categoryIds()
            ));

            if (array_key_exists('variants', $data) && is_array($data['variants'])) {
                $currentVariants = collect($current->variants())->keyBy(
                    fn (ProductVariant $variant): int => (int) $variant->id()
                );
                $retainedIds = [];

                foreach ($data['variants'] as $index => $variantData) {
                    $variantId = ! empty($variantData['id']) ? (int) $variantData['id'] : null;
                    $oldVariant = $variantId ? $currentVariants->get($variantId) : null;

                    if ($variantId !== null && ! $oldVariant) {
                        throw new InvalidArgumentException('Variant tidak termasuk dalam produk yang sedang diperbarui.');
                    }

                    if ($variantId === null && ! empty($variantData['sku'])) {
                        $oldVariant = $currentVariants->first(
                            fn (ProductVariant $variant): bool => $variant->sku() === (string) $variantData['sku']
                        );
                        $variantId = $oldVariant?->id();
                    }

                    $computedSku = trim((string) ($variantData['sku'] ?? ($oldVariant?->sku() ?? '')));

                    if ($computedSku === '') {
                        $computedSku = $this->generateSku([
                            'name' => $variantData['name'] ?? $product->name(),
                            'brand' => $product->brand(),
                            'primary_category_id' => $product->primaryCategoryId(),
                        ], $storeId);
                    }

                    $saved = $this->variants->save(new ProductVariant(
                        id: $variantId,
                        productId: (int) $product->id(),
                        storeId: $storeId,
                        sku: $computedSku,
                        name: Str::lower(trim((string) ($variantData['name'] ?? ($oldVariant?->name() ?? $product->name())))),
                        price: (float) ($variantData['price'] ?? ($oldVariant?->price() ?? 0.0)),
                        stock: (int) ($variantData['stock'] ?? ($oldVariant?->stock() ?? 0)),
                        isDefault: $index === 0
                    ));

                    $savedId = (int) $saved->id();
                    $retainedIds[] = $savedId;

                    if (array_key_exists('values', $variantData)) {
                        $this->variants->replaceValues($savedId, $variantData['values'] ?? []);
                    }
                }

                foreach ($currentVariants as $currentVariant) {
                    if (! in_array((int) $currentVariant->id(), $retainedIds, true)) {
                        $this->variants->delete((int) $currentVariant->id());
                    }
                }
            }

            if (array_key_exists('images', $data)) {
                $this->productImages->replaceForProduct((int) $product->id(), $data['images'] ?? []);
            }

            if (array_key_exists('attribute_values', $data)) {
                $this->attributeValues->replaceForProduct(
                    (int) $product->id(),
                    $this->normalizeAttributeValues($data['attribute_values'] ?? [])
                );
            }

            return $this->products->findById((int) $product->id(), true);
        });
    }

    private function generateSku(array $payload, int $storeId): string
    {
        $parts = [];

        if (! empty($payload['name'])) {
            $parts[] = (string) $payload['name'];
        }

        if (! empty($payload['brand'])) {
            $parts[] = (string) $payload['brand'];
        }

        if (! empty($payload['primary_category_id'])) {
            $parts[] = 'CAT' . (string) $payload['primary_category_id'];
        }

        $base = Str::upper(Str::slug(implode('-', array_filter($parts))));
        $base = $base === '' ? 'PRODUCT' : Str::substr($base, 0, 40);
        $date = now()->format('ymd');
        $counter = 1;

        do {
            $sku = $base . '-' . $date . '-' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $exists = DB::table('product_variants')
                ->where('sku', $sku)
                ->where('store_id', $storeId)
                ->exists();
            $counter++;
        } while ($exists);

        return $sku;
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
                    $rows[] = ['attribute_id' => (int) $item['attribute_id'], 'value' => (string) $value];
                }

                continue;
            }

            if (array_key_exists('value', $item)) {
                $rows[] = ['attribute_id' => (int) $item['attribute_id'], 'value' => (string) $item['value']];
            }
        }

        return $rows;
    }
}
