<?php

namespace App\Domains\Catalog\Application\UseCases\Store;

use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;

class GetStoreUseCase
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}

    public function execute()
    {
        return $this->repository->paginate();
    }
}
