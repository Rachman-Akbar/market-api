<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\Product;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteSellerProductUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $productId): void
    {
        $store = $this->stores->execute($user);

        $deleted = DB::table('products')
            ->where('id', $productId)
            ->where('store_id', $store->id)
            ->where('seller_id', $user->id)
            ->delete();

        if ($deleted === 0) {
            abort(404, 'Product not found.');
        }
    }
}
