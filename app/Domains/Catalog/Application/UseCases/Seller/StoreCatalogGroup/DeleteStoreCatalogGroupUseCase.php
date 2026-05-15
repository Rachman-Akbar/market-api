<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteStoreCatalogGroupUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $groupId): void
    {
        $store = $this->stores->execute($user);

        $deleted = DB::table('store_catalog_groups')
            ->where('store_id', $store->id)
            ->where('id', $groupId)
            ->delete();

        if ($deleted === 0) {
            abort(404, 'Store catalog group not found.');
        }
    }
}
