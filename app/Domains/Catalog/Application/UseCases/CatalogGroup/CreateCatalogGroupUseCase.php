<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;

class CreateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(array $data)
    {
        $group = new CatalogGroup(
            id: null,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
        );

        return $this->repository->create($group);
    }
}
