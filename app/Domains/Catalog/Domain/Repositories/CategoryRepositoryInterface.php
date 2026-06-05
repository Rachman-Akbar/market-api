<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories as a tree structure
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
     * Menu tree based on catalog group
     */
    public function getMenuTree(?int $catalogGroupId = null): Collection;

    /**
     * Save category (Handles both Create and Update inside Domain Layer)
     */
    public function save(Category $category): Category;

    /**
     * Delete category by ID
     */
    public function delete(int $id): bool;

    /**
     * Catalog groups with active categories
     */
    public function getAllWithCategories(): Collection;

    /**
     * Mega menu structure for public header
     */
    public function getHeaderMenu(): Collection;

    /**
     * Find category by its full URL path (e.g., electronic/computer/laptop)
     */
    public function findByPath(string $path): ?Category;
}
