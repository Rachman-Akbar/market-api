<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;

final class GetCatalogGroupBySlugUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(string $slug): ?CatalogGroup
    {
        return $this->repository->findBySlug($slug);
    }
}