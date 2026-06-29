<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\UseCases\Product;

use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductAttributeValueRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductImageRepositoryInterface;

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
        return DB::transaction(function () use ($id, $data) {
            $current = $this->products->findById((int) $id);

            abort_if(! $current, 404, 'Product not found.');

            $storeId = (int) ($data['store_id'] ?? $current->storeId());

            // Update data utama Product (Tanpa sellerId)
            $product = $this->products->save(new Product(
                id: $current->id(),
                storeId: $storeId,
                primaryCategoryId: array_key_exists('primary_category_id', $data) ? ($data['primary_category_id'] ? (int) $data['primary_category_id'] : null) : $current->primaryCategoryId(),
                name: (string) ($data['name'] ?? $current->name()),
                slug: (string) ($data['slug'] ?? $current->slug()),
                description: array_key_exists('description', $data) ? $data['description'] : $current->description(),
                brand: array_key_exists('brand', $data) ? $data['brand'] : $current->brand(),
                thumbnail: array_key_exists('thumbnail', $data) ? $data['thumbnail'] : $current->thumbnail(),
                status: (string) ($data['status'] ?? $current->status()),
                isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $current->isActive(),
                categoryIds: array_key_exists('category_ids', $data) ? array_map('intval', $data['category_ids'] ?? []) : $current->categoryIds()
            ));

            if (array_key_exists('variants', $data) && is_array($data['variants'])) {
                $currentVariants = $current->variants();

                // 1. Tambahkan method resolveSku dan generateSku (copas dari CreateProductUseCase ke UpdateProductUseCase)

// 2. Ubah loop variants menjadi seperti ini:
foreach ($data['variants'] as $variantData) {
    $variantId = null;

    if (! empty($variantData['id'])) {
        $variantId = (int) $variantData['id'];
    } else {
        $matched = collect($currentVariants)->first(
            fn (ProductVariant $v) => $v->sku() === ($variantData['sku'] ?? '')
        );
        $variantId = $matched ? $matched->id() : null;
    }

    $oldVariant = $variantId 
        ? collect($currentVariants)->first(fn (ProductVariant $v) => $v->id() === $variantId)
        : null;

    // AMAN: Jika sku kosong pada variant baru/lama, generate otomatis
    $computedSku = (string) ($variantData['sku'] ?? ($oldVariant ? $oldVariant->sku() : ''));
    if ($computedSku === '') {
        // Gunakan nama variant baru atau fallback ke nama product
        $payloadForSku = [
            'name' => $variantData['name'] ?? $product->name(),
            'brand' => $product->brand(),
            'primary_category_id' => $product->primaryCategoryId()
        ];
        $computedSku = $this->generateSku($payloadForSku, $storeId);
    }

    $this->variants->save(new ProductVariant(
        id: $variantId,
        productId: (int) $product->id(),
        storeId: $storeId,
        sku: $computedSku, // Gunakan sku hasil kalkulasi aman
        name: (string) ($variantData['name'] ?? ($oldVariant ? $oldVariant->name() : $product->name())),
        price: (float) ($variantData['price'] ?? ($oldVariant ? $oldVariant->price() : 0.0)),
        stock: (int) ($variantData['stock'] ?? ($oldVariant ? $oldVariant->stock() : 0)),
        isDefault: array_key_exists('is_default', $variantData) 
            ? (bool) $variantData['is_default'] 
            : ($oldVariant ? $oldVariant->isDefault() : false)
    ));
}
            }

            if (array_key_exists('images', $data)) {
                $this->productImages->replaceForProduct(
                    productId: (int) $product->id(),
                    images: $data['images'] ?? []
                );
            }

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
            if (! isset($item['attribute_id'])) continue;
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