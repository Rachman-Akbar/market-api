<?php

namespace App\Domains\Stores\Application\UseCases;

use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Stores\Application\DTOs\StoreData;

final class CreateStoreUseCase
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}

    public function execute(StoreData $data)
    {
        return $this->repository->create($data);
    }
}
