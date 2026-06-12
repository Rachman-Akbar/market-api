<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\UseCases;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Stores\Application\ReadModels\Store\StoreCatalogReaderInterface;

final class ListStoreUseCase
{
    public function __construct(
        private StoreCatalogReaderInterface $reader
    ) {}

    public function execute(array $filters = []): LengthAwarePaginator
    {
        return $this->reader->paginatedStores($filters);
    }
}