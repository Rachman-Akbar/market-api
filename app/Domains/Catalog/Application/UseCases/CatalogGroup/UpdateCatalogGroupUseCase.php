<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;

final class UpdateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data)
    {
        return $this->repository->update($id, [
            'name' => $data['name'] ?? null,
            'slug' => $data['slug'] ?? null,
            'is_active' => $data['is_active'] ?? null,
        ]);
    }
}


