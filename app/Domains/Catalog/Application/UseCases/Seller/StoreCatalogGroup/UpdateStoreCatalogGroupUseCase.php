<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UpdateStoreCatalogGroupUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $groupId, array $data): object
    {
        $store = $this->stores->execute($user);

        $group = DB::table('store_catalog_groups')
            ->where('store_id', $store->id)
            ->where('id', $groupId)
            ->first();

        if ($group === null) {
            abort(404, 'Store catalog group not found.');
        }

        $updateData = [];

        foreach (['name', 'description', 'thumbnail', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (array_key_exists('slug', $data)) {
            $updateData['slug'] = $this->makeUniqueSlug($store->id, $data['slug'], (int) $groupId);
        }

        if ($updateData !== []) {
            $updateData['updated_at'] = now();

            DB::table('store_catalog_groups')
                ->where('id', $groupId)
                ->update($updateData);
        }

        return DB::table('store_catalog_groups')
            ->where('id', $groupId)
            ->first();
    }

    private function makeUniqueSlug(int $storeId, string $source, int $ignoreId): string
    {
        $baseSlug = Str::slug($source) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            DB::table('store_catalog_groups')
                ->where('store_id', $storeId)
                ->where('slug', $slug)
                ->where('id', '!=', $ignoreId)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
