<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\ReadModels\Product;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use App\Domains\Stores\Application\ReadModels\Product\ProductCatalogReaderInterface;

final class EloquentProductCatalogReader implements ProductCatalogReaderInterface
{
    public function publishedProductsByStoreSlug(
        string $slug,
        array $filters = []
    ): LengthAwarePaginatorContract {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);
        $page = (int) ($filters['page'] ?? 1);

        $query = DB::table('products')
            ->join('stores', 'stores.id', '=', 'products.store_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('stores.slug', $slug)
            ->where('products.status', 'published')
            ->select([
                'products.id',
                'products.store_id',
                'products.category_id',
                'products.seller_id',
                'products.name',
                'products.slug',
                'products.description',
                'products.price',
                'products.stock',
                'products.thumbnail',
                'products.status',
                'products.created_at',
                'products.updated_at',

                'stores.id as store_ref_id',
                'stores.name as store_name',
                'stores.slug as store_slug',
                'stores.logo_url as store_logo_url',

                'categories.id as category_ref_id',
                'categories.name as category_name',
                'categories.slug as category_slug',
            ])
            ->orderByDesc('products.created_at');

        $paginator = $query->paginate(
            perPage: $perPage,
            columns: ['*'],
            pageName: 'page',
            page: $page
        );

        $productIds = collect($paginator->items())
            ->pluck('id')
            ->all();

        $imagesByProductId = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id');

        $items = collect($paginator->items())
            ->map(function (object $row) use ($imagesByProductId): array {
                return [
                    'id' => $row->id,
                    'store_id' => $row->store_id,
                    'category_id' => $row->category_id,
                    'seller_id' => $row->seller_id,

                    'name' => $row->name,
                    'slug' => $row->slug,
                    'description' => $row->description,
                    'price' => $row->price,
                    'stock' => $row->stock,
                    'thumbnail' => $row->thumbnail,
                    'status' => $row->status,

                    'store' => [
                        'id' => $row->store_ref_id,
                        'name' => $row->store_name,
                        'slug' => $row->store_slug,
                        'logo_url' => $row->store_logo_url,
                    ],

                    'category' => $row->category_ref_id ? [
                        'id' => $row->category_ref_id,
                        'name' => $row->category_name,
                        'slug' => $row->category_slug,
                    ] : null,

                    'images' => $imagesByProductId
                        ->get($row->id, collect())
                        ->map(fn (object $image): array => [
                            'id' => $image->id,
                            'image_url' => $image->image_url ?? $image->url ?? null,
                            'url' => $image->url ?? $image->image_url ?? null,
                            'is_primary' => (bool) $image->is_primary,
                        ])
                        ->values()
                        ->all(),

                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            })
            ->all();

        return new LengthAwarePaginator(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
