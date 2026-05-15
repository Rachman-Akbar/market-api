<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCategory;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateStoreCategoryUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $categoryId, array $data): object
    {
        $store = $this->stores->execute($user);

        $category = DB::table('store_categories')
            ->where('store_id', $store->id)
            ->where('id', $categoryId)
            ->first();

        if ($category === null) {
            abort(404, 'Store category not found.');
        }

        if (! empty($data['parent_id'])) {
            if ((int) $data['parent_id'] === (int) $categoryId) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Category cannot be its own parent.'],
                ]);
            }

            $this->assertParentBelongsToStore($store->id, (int) $data['parent_id']);
        }

        $updateData = [];

        foreach (['parent_id', 'name', 'description', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (array_key_exists('slug', $data)) {
            $updateData['slug'] = $this->makeUniqueSlug($store->id, $data['slug'], (int) $categoryId);
        }

        if ($updateData !== []) {
            $updateData['updated_at'] = now();

            DB::table('store_categories')
                ->where('id', $categoryId)
                ->update($updateData);
        }

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

    private function makeUniqueSlug(int $storeId, string $source, int $ignoreId): string
    {
        $baseSlug = Str::slug($source) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            DB::table('store_categories')
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
