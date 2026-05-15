<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCategory;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ListStoreCategoriesUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, array $filters = []): Collection
    {
        $store = $this->stores->execute($user);

        $query = DB::table('store_categories')
            ->where('store_id', $store->id);

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
