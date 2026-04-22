<?php

namespace App\Domains\Catalog\Application\UseCases\Store;

use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Catalog\Application\DTOs\StoreData;
use App\Domains\Catalog\Application\DTOs\StoreDetailData;

class CreateStoreUseCase
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}

    public function execute(StoreDetailData $data)
    {
        return $this->repository->create($data);
    }
}
