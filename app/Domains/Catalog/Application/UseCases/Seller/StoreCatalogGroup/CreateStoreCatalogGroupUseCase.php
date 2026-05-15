<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateStoreCatalogGroupUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, array $data): object
    {
        $store = $this->stores->execute($user);

        $groupId = DB::table('store_catalog_groups')->insertGetId([
            'store_id' => $store->id,
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($store->id, $data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'thumbnail' => $data['thumbnail'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('store_catalog_groups')
            ->where('id', $groupId)
            ->first();
    }

    private function makeUniqueSlug(int $storeId, string $source): string
    {
        $baseSlug = Str::slug($source) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            DB::table('store_catalog_groups')
                ->where('store_id', $storeId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
