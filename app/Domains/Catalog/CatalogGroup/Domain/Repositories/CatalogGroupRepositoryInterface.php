<?php

namespace App\Domains\Catalog\CatalogGroup\Domain\Repositories;

use Illuminate\Support\Collection;
use App\Domains\Catalog\CatalogGroup\Domain\Entities\CatalogGroup;

interface CatalogGroupRepositoryInterface
{
    public function getAll(array $filters = []): Collection;

    public function findById(int $id): ?CatalogGroup;

    public function findBySlug(string $slug): ?CatalogGroup;

    public function getCategoriesByGroupId(int $groupId): Collection;

    /**
     * Kontrak tunggal untuk persistence state domain (Create & Update)
     */
    public function save(CatalogGroup $catalogGroup): CatalogGroup;

    public function delete(int $id): bool;

    public function clearCache(): void;
}
