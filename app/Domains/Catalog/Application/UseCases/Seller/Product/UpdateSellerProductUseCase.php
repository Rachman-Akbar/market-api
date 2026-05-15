<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\Product;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateSellerProductUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
        private readonly ShowSellerProductUseCase $showProduct,
    ) {}

    public function execute(User $user, int|string $productId, array $data): array
    {
        $store = $this->stores->execute($user);

        $product = DB::table('products')
            ->where('id', $productId)
            ->where('store_id', $store->id)
            ->where('seller_id', $user->id)
            ->first();

        if ($product === null) {
            abort(404, 'Product not found.');
        }

        $productData = $this->onlyProductFields($data);

        if (array_key_exists('slug', $data)) {
            $productData['slug'] = $this->makeUniqueProductSlug($data['slug'], (int) $productId);
        }

        DB::transaction(function () use ($store, $productId, $data, $productData) {
            if ($productData !== []) {
                $productData['updated_at'] = now();

                DB::table('products')
                    ->where('id', $productId)
                    ->update($productData);
            }

            if (array_key_exists('store_category_ids', $data)) {
                $ids = $this->normalizeIds($data['store_category_ids'] ?? []);
                $this->assertStoreCategoriesBelongToStore($store->id, $ids);
                $this->syncStoreCategories((int) $productId, $ids);
            }

            if (array_key_exists('store_catalog_group_ids', $data)) {
                $ids = $this->normalizeIds($data['store_catalog_group_ids'] ?? []);
                $this->assertStoreCatalogGroupsBelongToStore($store->id, $ids);
                $this->syncStoreCatalogGroups((int) $productId, $ids);
            }
        });

        return $this->showProduct->execute($user, $productId);
    }

    private function onlyProductFields(array $data): array
    {
        $fields = [
            'primary_category_id',
            'name',
            'sku',
            'description',
            'short_description',
            'brand',
            'weight_gram',
            'price',
            'stock',
            'thumbnail',
            'status',
            'is_featured',
            'is_active',
        ];

        $result = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            }
        }

        return $result;
    }

    private function makeUniqueProductSlug(string $source, int $ignoreProductId): string
    {
        $baseSlug = Str::slug($source) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            DB::table('products')
                ->where('slug', $slug)
                ->where('id', '!=', $ignoreProductId)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function assertStoreCategoriesBelongToStore(int $storeId, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $count = DB::table('store_categories')
            ->where('store_id', $storeId)
            ->whereIn('id', $ids)
            ->count();

        if ($count !== count($ids)) {
            throw ValidationException::withMessages([
                'store_category_ids' => ['One or more store categories are invalid.'],
            ]);
        }
    }

    private function assertStoreCatalogGroupsBelongToStore(int $storeId, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $count = DB::table('store_catalog_groups')
            ->where('store_id', $storeId)
            ->whereIn('id', $ids)
            ->count();

        if ($count !== count($ids)) {
            throw ValidationException::withMessages([
                'store_catalog_group_ids' => ['One or more store catalog groups are invalid.'],
            ]);
        }
    }

    private function syncStoreCategories(int $productId, array $ids): void
    {
        DB::table('store_category_product')
            ->where('product_id', $productId)
            ->delete();

        if ($ids === []) {
            return;
        }

        DB::table('store_category_product')->insert(
            array_map(fn (int $id) => [
                'store_category_id' => $id,
                'product_id' => $productId,
                'created_at' => now(),
            ], $ids)
        );
    }

    private function syncStoreCatalogGroups(int $productId, array $ids): void
    {
        DB::table('store_catalog_group_product')
            ->where('product_id', $productId)
            ->delete();

        if ($ids === []) {
            return;
        }

        DB::table('store_catalog_group_product')->insert(
            array_map(fn (int $id) => [
                'store_catalog_group_id' => $id,
                'product_id' => $productId,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ], $ids)
        );
    }
}
