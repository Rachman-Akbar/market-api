<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\ReadModels\Product;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Stores\Application\ReadModels\Product\ProductCatalogReaderInterface;

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
            ->where('stores.slug', $slug)
            ->where('products.status', 'published')
            ->where('products.is_active', true)
            ->select([
                'products.id',
                'products.store_id',
                'products.primary_category_id',
                'products.seller_id',
                'products.name',
                'products.slug',
                'products.description',
                'products.short_description',
                'products.brand',
                'products.price',
                'products.stock',
                'products.thumbnail',
                'products.status',
                'products.is_featured',
                'products.is_active',
                'products.created_at',
                'products.updated_at',

                DB::raw("
                    JSON_OBJECT(
                        'id', categories.id,
                        'name', categories.name,
                        'slug', categories.slug,
                        'full_slug', categories.full_slug
                    ) as category
                "),

                DB::raw("
                    JSON_OBJECT(
                        'id', stores.id,
                        'name', stores.name,
                        'slug', stores.slug,
                        'logo_url', stores.logo
                    ) as store
                "),
            ]);

        if (! empty($filters['search'])) {
            $query->where('products.name', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('products.primary_category_id', (int) $filters['category_id']);
        }

        return $query
            ->latest('products.created_at')
            ->paginate($perPage);
    }
}
