<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\Product;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateSellerProductUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
        private readonly ShowSellerProductUseCase $showProduct,
    ) {}

    public function execute(User $user, array $data): array
    {
        $store = $this->stores->execute($user);

        $storeCategoryIds = $this->normalizeIds($data['store_category_ids'] ?? []);
        $storeCatalogGroupIds = $this->normalizeIds($data['store_catalog_group_ids'] ?? []);

        $this->assertStoreCategoriesBelongToStore($store->id, $storeCategoryIds);
        $this->assertStoreCatalogGroupsBelongToStore($store->id, $storeCatalogGroupIds);

        $productId = DB::transaction(function () use ($user, $store, $data, $storeCategoryIds, $storeCatalogGroupIds) {
            $productId = DB::table('products')->insertGetId([
                'store_id' => $store->id,
                'seller_id' => $user->id,
                'primary_category_id' => $data['primary_category_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->makeUniqueProductSlug($data['slug'] ?? $data['name']),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'weight_gram' => $data['weight_gram'] ?? null,
                'price' => $data['price'],
                'stock' => $data['stock'] ?? 0,
                'thumbnail' => $data['thumbnail'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncStoreCategories($productId, $storeCategoryIds);
            $this->syncStoreCatalogGroups($productId, $storeCatalogGroupIds);

            return $productId;
        });

        return $this->showProduct->execute($user, $productId);
    }

    private function makeUniqueProductSlug(string $source): string
    {
        $baseSlug = Str::slug($source) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (DB::table('products')->where('slug', $slug)->exists()) {
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
