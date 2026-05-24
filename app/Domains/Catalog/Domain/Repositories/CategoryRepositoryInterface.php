<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Find category by ID
     */
    public function findById(int $id): ?Category;

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Menu tree
     */
    public function getMenuTree(?int $catalogGroupId = null): Collection;

    /**
     * Create category
     */
    public function create(Category $category): Category;

    /**
     * Update category
     */
    public function update(int $id, array $data): Category;

    /**
     * Delete category
     */
    public function delete(int $id): bool;

    /**
     * Catalog groups with categories
     */
    public function getAllWithCategories(): Collection;

    public function getHeaderMenu(): Collection;

    public function findByPath(string $path): ?Category;

    
}

