<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\UseCases;

use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;

final class GetStoreBySlugUseCase
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}

    public function execute(string $slug)
    {
        return $this->repository->findBySlug($slug);
    }
}
