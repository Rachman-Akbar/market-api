<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;

class UpdateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(string $id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}