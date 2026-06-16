<?php

namespace App\Domains\Catalog\CatalogGroup\Application\Queries;

use App\Domains\Catalog\CatalogGroup\Domain\Repositories\CatalogGroupRepositoryInterface;

final class GetCatalogGroupDetailQuery
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(int $id)
    {
        return $this->repository->findById($id);
    }
}

