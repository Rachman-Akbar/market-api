<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCategories;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateStoreCategoryUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, array $data): object
    {
        $store = $this->stores->execute($user);

        if (! empty($data['parent_id'])) {
            $this->assertParentBelongsToStore($store->id, (int) $data['parent_id']);
        }

        $categoryId = DB::table('store_categories')->insertGetId([
            'store_id' => $store->id,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($store->id, $data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('store_categories')
            ->where('id', $categoryId)
            ->first();
    }

    private function assertParentBelongsToStore(int $storeId, int $parentId): void
    {
        $exists = DB::table('store_categories')
            ->where('store_id', $storeId)
            ->where('id', $parentId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'parent_id' => ['Parent category is invalid.'],
            ]);
        }
    }

    private function makeUniqueSlug(int $storeId, string $source): string
    {
        $baseSlug = Str::slug($source) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            DB::table('store_categories')
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
