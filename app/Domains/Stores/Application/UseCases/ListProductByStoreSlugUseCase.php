<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\UseCases;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Stores\Application\ReadModels\Product\ProductCatalogReaderInterface;

final readonly class ListProductByStoreSlugUseCase
{
    public function __construct(
        private ProductCatalogReaderInterface $products
    ) {
    }

    public function execute(string $slug, array $filters = []): LengthAwarePaginator
    {
        return $this->products->publishedProductsByStoreSlug($slug, $filters);
    }
}
