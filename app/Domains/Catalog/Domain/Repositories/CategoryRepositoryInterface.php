<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function findById(string $id): ?Category;

    public function save(Category $category): Category;

    public function delete(string $id): bool;
}