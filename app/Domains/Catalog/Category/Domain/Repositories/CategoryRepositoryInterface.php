<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Domain\Repositories;

use App\Domains\Catalog\Category\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function findByFullSlug(string $fullSlug): ?Category;

    public function listTree(): array;

    public function listMenuTree(): array;

    public function findChildrenByParentId(int $parentId): array;

    public function isDescendantOf(int $categoryId, int $possibleDescendantId): bool;

    public function maxDepthFrom(int $categoryId): int;

    public function save(Category $category): Category;

    public function delete(int $id): bool;
}