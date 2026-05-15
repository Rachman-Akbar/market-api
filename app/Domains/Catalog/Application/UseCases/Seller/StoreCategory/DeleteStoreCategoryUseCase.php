<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCategory;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteStoreCategoryUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $categoryId): void
    {
        $store = $this->stores->execute($user);

        $deleted = DB::table('store_categories')
            ->where('store_id', $store->id)
            ->where('id', $categoryId)
            ->delete();

        if ($deleted === 0) {
            abort(404, 'Store category not found.');
        }
    }
}
