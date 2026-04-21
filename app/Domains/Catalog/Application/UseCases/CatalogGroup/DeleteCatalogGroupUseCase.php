<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;

class DeleteCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(string $id): bool
    {
        return $this->repository->delete($id);
    }
}