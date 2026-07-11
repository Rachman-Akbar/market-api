<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Category\Domain\Entities\Category;
use App\Domains\Catalog\Category\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Mappers\CategoryMapper;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    private const CACHE_TTL = 1800;

    public function findById(int $id): ?Category
    {
        $model = CategoryModel::query()
            ->with('childrenTree')
            ->find($id);

        return $this->toEntity($model);
    }

    public function findBySlug(string $slug): ?Category
    {
        $slug = $this->normalizePath($slug);

        if ($slug === '') {
            return null;
        }

        $model = CategoryModel::query()
            ->with('childrenTree')
            ->where('slug', $slug)
            ->first();

        return $this->toEntity($model);
    }

    public function findByPath(string $path): ?Category
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            return null;
        }

        $model = CategoryModel::query()
            ->with('childrenTree')
            ->where('full_slug', $path)
            ->when(! str_contains($path, '/'), function ($query) use ($path) {
                $query->orWhere('slug', $path);
            })
            ->first();

        return $this->toEntity($model);
    }

    public function findByFullSlug(string $fullSlug): ?Category
    {
        return $this->findByPath($fullSlug);
    }

    public function listTree(): array
    {
        $rows = Cache::remember('catalog_categories_tree_rows_v2', self::CACHE_TTL, function () {
            return $this->categoryRows(false);
        });

        return $this->buildTreeFromRows($rows);
    }

    public function listMenuTree(): array
    {
        $rows = Cache::remember('catalog_categories_menu_rows_v2', self::CACHE_TTL, function () {
            return $this->categoryRows(true);
        });

        return $this->buildTreeFromRows($rows);
    }

    public function findChildrenByParentId(int $parentId): array
    {
        return CategoryModel::query()
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CategoryModel $model) => CategoryMapper::toEntity($model))
            ->all();
    }

    public function isDescendantOf(int $categoryId, int $possibleDescendantId): bool
    {
        if ($categoryId === $possibleDescendantId) {
            return false;
        }

        $current = CategoryModel::query()->find($possibleDescendantId);

        while ($current && $current->parent_id !== null) {
            if ((int) $current->parent_id === $categoryId) {
                return true;
            }

            $current = CategoryModel::query()->find((int) $current->parent_id);
        }

        return false;
    }

    public function maxDepthFrom(int $categoryId): int
    {
        $childIds = CategoryModel::query()
            ->where('parent_id', $categoryId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($childIds === []) {
            return 1;
        }

        $maxDepth = 1;

        foreach ($childIds as $childId) {
            $maxDepth = max($maxDepth, 1 + $this->maxDepthFrom($childId));
        }

        return $maxDepth;
    }

    public function save(Category $category): Category
    {
        $model = $category->id()
            ? CategoryModel::query()->findOrFail($category->id())
            : new CategoryModel();

        $model->catalog_group_id = $category->catalogGroupId();
        $model->parent_id = $category->parentId();
        $model->level = $category->level();
        $model->sort_order = $category->sortOrder();
        $model->is_active = $category->isActive();
        $model->is_visible_in_menu = $category->isVisibleInMenu();
        $model->name = $category->name();
        $model->slug = $category->slug();
        $model->full_slug = $category->fullSlug();
        $model->image_url = $category->imageUrl();
        $model->icon_url = $category->iconUrl();

        $model->save();
        $this->clearCache();

        return CategoryMapper::toEntity(
            $model->refresh()->load('childrenTree')
        );
    }

    public function delete(int $id): bool
    {
        $model = CategoryModel::query()->find($id);

        if (! $model) {
            return false;
        }

        $deleted = (bool) $model->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    private function toEntity(?CategoryModel $model): ?Category
    {
        return $model ? CategoryMapper::toEntity($model) : null;
    }

    private function normalizePath(string $path): string
    {
        return trim(rawurldecode($path), '/');
    }

    private function categoryRows(bool $menuOnly): array
    {
        return CategoryModel::query()
            ->select([
                'id',
                'catalog_group_id',
                'parent_id',
                'level',
                'sort_order',
                'is_active',
                'is_visible_in_menu',
                'name',
                'slug',
                'full_slug',
                'image_url',
                'icon_url',
            ])
            ->withCount('products')
            ->where('level', '<=', 3)
            ->when($menuOnly, function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('is_visible_in_menu', true);
            })
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CategoryModel $model) => [
                'id' => (int) $model->id,
                'catalog_group_id' => (int) $model->catalog_group_id,
                'parent_id' => $model->parent_id !== null ? (int) $model->parent_id : null,
                'level' => (int) $model->level,
                'sort_order' => (int) $model->sort_order,
                'is_active' => (bool) $model->is_active,
                'is_visible_in_menu' => (bool) $model->is_visible_in_menu,
                'name' => (string) $model->name,
                'slug' => (string) $model->slug,
                'full_slug' => (string) $model->full_slug,
                'image_url' => $model->image_url,
                'icon_url' => $model->icon_url,
                'products_count' => (int) ($model->products_count ?? 0),
            ])
            ->all();
    }

    private function buildTree(Collection $models): array
    {
        return $this->buildTreeFromRows(
            $models->map(fn (CategoryModel $model) => [
                'id' => (int) $model->id,
                'catalog_group_id' => (int) $model->catalog_group_id,
                'parent_id' => $model->parent_id !== null ? (int) $model->parent_id : null,
                'level' => (int) $model->level,
                'sort_order' => (int) $model->sort_order,
                'is_active' => (bool) $model->is_active,
                'is_visible_in_menu' => (bool) $model->is_visible_in_menu,
                'name' => (string) $model->name,
                'slug' => (string) $model->slug,
                'full_slug' => (string) $model->full_slug,
                'image_url' => $model->image_url,
                'icon_url' => $model->icon_url,
                'products_count' => (int) ($model->products_count ?? 0),
            ])->all()
        );
    }

    private function buildTreeFromRows(array $rows): array
    {
        $entities = [];
        $roots = [];

        foreach ($rows as $row) {
            $entities[(int) $row['id']] = CategoryMapper::toEntityFromArray($row);
        }

        foreach ($entities as $entity) {
            $parentId = $entity->parentId();

            if ($parentId !== null && isset($entities[$parentId])) {
                $entities[$parentId]->addChild($entity);
                continue;
            }

            $roots[] = $entity;
        }

        return $roots;
    }

    private function clearCache(): void
    {
        Cache::forget('catalog_categories_tree_rows_v2');
        Cache::forget('catalog_categories_menu_rows_v2');
    }
}
