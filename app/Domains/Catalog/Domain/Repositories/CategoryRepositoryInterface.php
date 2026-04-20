<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function findById(string $id);

    public function create(array $data);

    public function update(string $id, array $data);

    public function delete(string $id): bool;
}