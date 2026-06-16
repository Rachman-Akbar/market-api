<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Category\Domain\Entities\Category;
use App\Domains\Catalog\Category\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Mappers\CategoryMapper;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?Category
    {
        $model = CategoryModel::query()
            ->with('childrenTree')
            ->find($id);

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Category
    {
        $model = CategoryModel::query()
            ->with('childrenTree')
            ->where('slug', $slug)
            ->first();

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function findByFullSlug(string $fullSlug): ?Category
    {
        $path = trim($fullSlug, '/');

        $model = CategoryModel::query()
            ->with('childrenTree')
            ->where('full_slug', $path)
            ->first();

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function listTree(): array
    {
        $models = CategoryModel::query()
            ->where('level', '<=', 3)
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->buildTree($models);
    }

    public function listMenuTree(): array
    {
        $models = CategoryModel::query()
            ->where('level', '<=', 3)
            ->where('is_active', true)
            ->where('is_visible_in_menu', true)
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->buildTree($models);
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

        return (bool) $model->delete();
    }

    private function buildTree($models): array
    {
        $entities = [];
        $roots = [];

        foreach ($models as $model) {
            $entities[(int) $model->id] = CategoryMapper::toEntity($model);
        }

        foreach ($entities as $entity) {
            $parentId = $entity->parentId();

            if ($parentId !== null && isset($entities[$parentId])) {
                $entities[$parentId]->addChild($entity);
            } else {
                $roots[] = $entity;
            }
        }

        return $roots;
    }
}