<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\Collection;

class GetCategoriesByCatalogGroupUseCase
{
    public function __construct(
        private readonly CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(int $catalogGroupId): Collection
    {
        return $this->repository->getCategoriesByGroupId($catalogGroupId);
    }
}