<?php

namespace App\Domains\Catalog\Domain\Repositories;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use Illuminate\Support\Collection;

interface CatalogGroupRepositoryInterface
{
    public function getAll(array $filters = []): Collection;

    public function findById(int $id): ?CatalogGroup;

    public function create(CatalogGroup $data): CatalogGroup;

    public function update(int $id, array $data): CatalogGroup;

    public function delete(int $id): bool;
}