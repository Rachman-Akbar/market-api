<?php

namespace App\Domains\Catalog\Application\UseCases\Seller\Product;

use App\Domains\Seller\Application\Actions\ResolveCurrentSellerStoreAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ShowSellerProductUseCase
{
    public function __construct(
        private readonly ResolveCurrentSellerStoreAction $stores,
    ) {}

    public function execute(User $user, int|string $productId): array
    {
        $store = $this->stores->execute($user);

        $product = DB::table('products')
            ->where('id', $productId)
            ->where('store_id', $store->id)
            ->where('seller_id', $user->id)
            ->first();

        if ($product === null) {
            abort(404, 'Product not found.');
        }

        return [
            'product' => $product,
            'store_categories' => DB::table('store_categories')
                ->join(
                    'store_category_product',
                    'store_categories.id',
                    '=',
                    'store_category_product.store_category_id'
                )
                ->where('store_category_product.product_id', $product->id)
                ->select('store_categories.*')
                ->orderBy('store_categories.sort_order')
                ->get(),

            'store_catalog_groups' => DB::table('store_catalog_groups')
                ->join(
                    'store_catalog_group_product',
                    'store_catalog_groups.id',
                    '=',
                    'store_catalog_group_product.store_catalog_group_id'
                )
                ->where('store_catalog_group_product.product_id', $product->id)
                ->select('store_catalog_groups.*', 'store_catalog_group_product.sort_order as pivot_sort_order')
                ->orderBy('store_catalog_group_product.sort_order')
                ->get(),
        ];
    }
}
