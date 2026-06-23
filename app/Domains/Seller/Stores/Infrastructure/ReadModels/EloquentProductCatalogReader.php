<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Infrastructure\ReadModels;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Seller\Stores\Application\ReadModels\ProductCatalogReaderInterface;

final class EloquentProductCatalogReader implements ProductCatalogReaderInterface
{
    public function publishedProductsByStoreSlug(
        string $slug,
        array $filters = []
    ): LengthAwarePaginator {
        $perPage = (int) ($filters['per_page'] ?? 12);

        $query = DB::table('products')
            ->join('stores', 'stores.id', '=', 'products.store_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.primary_category_id')
            // JOIN ke tabel product_variants untuk mengambil SKU, Price, dan Stock varian default
            ->leftJoin('product_variants', function ($join) {
                $join->on('product_variants.product_id', '=', 'products.id')
                     ->where('product_variants.is_default', '=', 1);
            })
            ->where('stores.slug', $slug)
            ->where('products.status', 'published')
            ->where('products.is_active', 1)
            ->select([
                'products.id',
                'products.store_id',
                'products.primary_category_id',
                'products.seller_id',
                'products.name',
                'products.slug',
                'products.description',
                'products.brand',
                'products.thumbnail',
                'products.status',
                'products.is_active',
                'products.created_at',
                'products.updated_at',

                // Data yang ditarik dari tabel varian
                'product_variants.sku as sku',
                'product_variants.price as price',
                'product_variants.stock as stock',

                // Format JSON Object untuk Category Relasi
                DB::raw("
                    JSON_OBJECT(
                        'id', categories.id,
                        'name', categories.name,
                        'slug', categories.slug,
                        'full_slug', categories.full_slug
                    ) as category
                "),

                // Format JSON Object untuk Store Relasi
                DB::raw("
                    JSON_OBJECT(
                        'id', stores.id,
                        'name', stores.name,
                        'slug', stores.slug,
                        'logo_url', stores.logo
                    ) as store
                "),
            ]);

        // Filter Pencarian Nama Produk
        if (! empty($filters['search'])) {
            $query->where('products.name', 'like', '%' . $filters['search'] . '%');
        }

        // Filter Berdasarkan Kategori
        if (! empty($filters['category_id'])) {
            $query->where('products.primary_category_id', (int) $filters['category_id']);
        }

        return $query
            ->latest('products.created_at')
            ->paginate($perPage);
    }
}
