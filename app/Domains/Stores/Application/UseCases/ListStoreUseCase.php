<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\UseCases;

use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;

final class ListStoreUseCase
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}

    public function execute(array $filters = [])
    {
        return $this->repository->paginate($filters);
    }
}
