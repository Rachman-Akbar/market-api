<?php

namespace App\Domains\Order\Wishlist\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Wishlist\Domain\Entities\Wishlist;
use App\Domains\Order\Wishlist\Domain\Repositories\WishlistRepositoryInterface;
use App\Domains\Order\Wishlist\Infrastructure\Persistence\Mappers\WishlistMapper;
use Illuminate\Support\Facades\DB;

class EloquentWishlistRepository implements WishlistRepositoryInterface
{
    public function findByUserId(string $userId): ?Wishlist
    {
        $wishlistRow = DB::table('wishlists')->where('user_id', $userId)->first();
        if (!$wishlistRow) return null;

        return WishlistMapper::toDomain($wishlistRow);
    }

    public function findItemsByUserId(string $userId): array
    {
    // Perbaikan pada baris join pertama: hapus tanda kurung () setelah nama tabel
    return DB::table('wishlist_items')
        ->join('wishlists', 'wishlist_items.wishlist_id', '=', 'wishlists.id')
        ->join('products', 'wishlist_items.product_id', '=', 'products.id')
        ->where('wishlists.user_id', $userId)
        ->select(
            'products.id',
            'products.name',
            'products.slug',
            'products.brand',
            'products.thumbnail',
            'products.status'
        )
        ->get()
        ->toArray();
    }

    public function save(Wishlist $wishlist): void
    {
        DB::transaction(function () use ($wishlist) {
            DB::table('wishlists')->updateOrInsert(
                ['id' => $wishlist->getId()],
                ['user_id' => $wishlist->getUserId(), 'name' => $wishlist->getName()]
            );

            DB::table('wishlist_items')->where('wishlist_id', $wishlist->getId())->delete();
            foreach ($wishlist->getItems() as $item) {
                DB::table('wishlist_items')->insert([
                    'wishlist_id' => $wishlist->getId(),
                    'product_id'  => $item->getProductId()
                ]);
            }
        });
    }
}
