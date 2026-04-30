<?php

namespace App\Domains\Stores\Application\UseCases;

use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;

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
