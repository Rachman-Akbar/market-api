<?php

namespace App\Domains\Catalog\CatalogGroup\Application\UseCases;

use App\Domains\Catalog\CatalogGroup\Application\Dtos\CatalogGroupData;
use App\Domains\Catalog\CatalogGroup\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\CatalogGroup\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\Str;

final class CreateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(CatalogGroupData $data): CatalogGroup
    {
        $name = $data->name();
        $slug = $data->slug() ?: Str::slug((string) $name);
        $isActive = $data->isActive() ?? true;

        $catalogGroup = new CatalogGroup(
            id: null,
            name: (string) $name,
            slug: $slug,
            isActive: $isActive
        );

        return $this->repository->save($catalogGroup);
    }
}