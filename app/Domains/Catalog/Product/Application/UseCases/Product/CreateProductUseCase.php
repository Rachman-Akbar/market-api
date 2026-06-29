<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\UseCases\Product;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Domain\Entities\Product;
use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductAttributeValueRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductImageRepositoryInterface;

final class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductVariantRepositoryInterface $variants,
        private readonly ProductAttributeValueRepositoryInterface $attributeValues,
        private readonly ProductImageRepositoryInterface $productImages
    ) {}

    public function execute(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $sellerId = $data['seller_id'];
            $storeId = $this->resolveStoreIdBySellerId($sellerId);

            if (! $storeId) {
                throw new \InvalidArgumentException('Toko untuk seller tersebut tidak ditemukan.');
            }

            $sku = $this->resolveSku($data, $storeId);

            // Simpan data utama Product (Tanpa sellerId)
            $product = $this->products->save(new Product(
                id: null,
                storeId: $storeId,
                primaryCategoryId: isset($data['primary_category_id']) ? (int) $data['primary_category_id'] : null,
                name: (string) $data['name'],
                slug: (string) ($data['slug'] ?? Str::slug((string) $data['name'])),
                description: $data['description'] ?? null,
                brand: $data['brand'] ?? null,
                thumbnail: $data['thumbnail'] ?? null,
                status: (string) ($data['status'] ?? 'published'),
                isActive: (bool) ($data['is_active'] ?? true),
                categoryIds: array_map('intval', $data['category_ids'] ?? [])
            ));

            if (! empty($data['images'])) {
                $this->productImages->replaceForProduct(
                    productId: (int) $product->id(),
                    images: $data['images']
                );
            }

            if (! empty($data['attribute_values'])) {
                $this->attributeValues->replaceForProduct(
                    productId: (int) $product->id(),
                    values: $this->normalizeAttributeValues($data['attribute_values'])
                );
            }

            // Tangani Pembuatan Varian dengan Store ID tersemat
            if (empty($data['variants'])) {
                $this->variants->save(new ProductVariant(
                    id: null,
                    productId: (int) $product->id(),
                    storeId: $storeId, // Wajib disertakan
                    sku: $sku,
                    name: (string) $data['name'],
                    price: (float) ($data['price'] ?? 0),
                    stock: (int) ($data['stock'] ?? 0),
                    isDefault: true
                ));
            } else {
                foreach ($data['variants'] as $variantData) {
                    $variant = $this->variants->save(new ProductVariant(
                        id: null,
                        productId: (int) $product->id(),
                        storeId: $storeId, // Wajib disertakan
                        sku: (string) ($variantData['sku'] ?? $sku),
                        name: (string) $variantData['name'],
                        price: (float) ($variantData['price'] ?? 0),
                        stock: (int) ($variantData['stock'] ?? 0),
                        isDefault: (bool) ($variantData['is_default'] ?? false)
                    ));

                    if (! empty($variantData['values'])) {
                        $this->variants->replaceValues(
                            variantId: (int) $variant->id(),
                            values: $variantData['values']
                        );
                    }
                }
            }

            return $this->products->findById((int) $product->id());
        });
    }

    private function resolveStoreIdBySellerId(string $sellerId): ?int
    {
        $storeId = DB::table('stores')->where('user_id', $sellerId)->value('id');
        return $storeId ? (int) $storeId : null;
    }

    private function resolveSku(array $payload, int $storeId): string
    {
        $sku = isset($payload['sku']) && is_string($payload['sku']) ? trim($payload['sku']) : '';
        return $sku !== '' ? $sku : $this->generateSku($payload, $storeId);
    }

    private function generateSku(array $payload, int $storeId): string
    {
        $parts = [];
        if (! empty($payload['name'])) $parts[] = (string) $payload['name'];
        if (! empty($payload['brand'])) $parts[] = (string) $payload['brand'];
        if (! empty($payload['primary_category_id'])) $parts[] = 'CAT' . (string) $payload['primary_category_id'];

        $base = Str::upper(Str::slug(implode('-', array_filter($parts))));
        $base = $base === '' ? 'PRODUCT' : Str::substr($base, 0, 40);

        $date = now()->format('ymd');
        $counter = 1;

        do {
            $sku = $base . '-' . $date . '-' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            // Cek keunikan SKU dipersempit hanya dalam cakupan Store ID yang sama
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