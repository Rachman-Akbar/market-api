<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function getMenuTree(?int $catalogGroupId = null): Collection;

    public function save(Category $category): Category;

    public function delete(int $id): bool;
}
