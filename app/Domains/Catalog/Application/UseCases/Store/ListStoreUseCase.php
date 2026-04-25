<?php

namespace App\Domains\Catalog\Application\UseCases\Store;

use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;

final class ListStoreUseCase
{
    public function __construct(
        private readonly StoreRepositoryInterface $stores
    ) {}

    public function execute(array $filters = [])
    {
        return $this->stores->listStores($filters);
    }
}