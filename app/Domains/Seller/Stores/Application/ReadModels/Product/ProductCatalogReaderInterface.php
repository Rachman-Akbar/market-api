<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\ReadModels\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductCatalogReaderInterface
{
    public function publishedProductsByStoreSlug(
        string $slug,
        array $filters = []
    ): LengthAwarePaginator;
}
