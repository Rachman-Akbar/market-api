<?php

namespace App\Domains\Catalog\CatalogGroup\Application\UseCases;

use App\Domains\Catalog\CatalogGroup\Application\Dtos\CatalogGroupData;
use App\Domains\Catalog\CatalogGroup\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\CatalogGroup\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\Str;

final class UpdateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(int $id, CatalogGroupData $data): ?CatalogGroup
    {
        $catalogGroup = $this->repository->findById($id);

        if (! $catalogGroup) {
            return null;
        }

        $name = $data->hasName()
            ? (string) $data->name()
            : $catalogGroup->name();

        $slug = match (true) {
            $data->hasSlug() => $data->slug() ?: Str::slug($name),
            $data->hasName() => Str::slug($name),
            default => $catalogGroup->slug(),
        };

        $isActive = $data->hasIsActive()
            ? (bool) $data->isActive()
            : $catalogGroup->isActive();

        $catalogGroup->updateData([
            'name' => $name,
            'slug' => $slug,
            'is_active' => $isActive,
        ]);

        return $this->repository->save($catalogGroup);
    }
}