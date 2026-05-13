<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\ReadModels\Store;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StoreCatalogReaderInterface
{
    public function paginatedStores(array $filters = []): LengthAwarePaginator;
}