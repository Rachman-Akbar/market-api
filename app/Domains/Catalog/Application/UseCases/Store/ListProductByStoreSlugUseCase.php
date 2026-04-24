<?php

namespace App\Domains\Catalog\Application\UseCases\Store;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

final class ListProductByStoreSlugUseCase
{
    public function __construct(
        private readonly StoreRepositoryInterface $stores,
        private readonly ProductRepositoryInterface $products
    ) {}

    public function execute(string $slug): Collection
    {
        $store = $this->stores->findBySlug($slug);

        abort_if(!$store, 404);

        return $this->products->findPublishedByStoreId($store->id());
    }
}