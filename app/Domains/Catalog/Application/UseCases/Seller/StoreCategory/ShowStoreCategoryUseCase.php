<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\StoreCategory;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ShowStoreCategoryUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $categoryId): object
    {
        $store = $this->stores->execute($user);

        $category = DB::table('store_categories')
            ->where('store_id', $store->id)
            ->where('id', $categoryId)
            ->first();

        if ($category === null) {
            abort(404, 'Store category not found.');
        }

        return $category;
    }
}
