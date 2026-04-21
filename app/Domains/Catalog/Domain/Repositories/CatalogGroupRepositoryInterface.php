<?php

namespace App\Domains\Catalog\Domain\Repositories;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use Illuminate\Support\Collection;

interface CatalogGroupRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(string $id): ?CatalogGroup;

    public function create(CatalogGroup $data): CatalogGroup;

    public function update(string $id, array $data): CatalogGroup;

    public function delete(string $id): bool;
}
