<?php

namespace App\Domains\Catalog\Application\UseCases\Store;

use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;

final class GetStoreUseCase
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}

    public function execute(array $filters = [], int $perPage = 15)
    {
        return $this->repository->paginate($filters, $perPage);
    }
}