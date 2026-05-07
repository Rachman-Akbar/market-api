<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CategoryModel::query()
            ->with([
                'catalogGroup',
                'parent',
            ])
            ->withCount('products');

        if (! empty($filters['catalog_group_id'])) {
            $query->where('catalog_group_id', $filters['catalog_group_id']);
        }

        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === null || $filters['parent_id'] === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        if (! empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('is_visible_in_menu', $filters)) {
            $query->where('is_visible_in_menu', filter_var($filters['is_visible_in_menu'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $paginator = $query
            ->orderBy('sort_order')
            ->latest()
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => CategoryMapper::toEntity($model))
        );

        return $paginator;
    }

    public function findById(int $id): ?Category
    {
        $model = CategoryModel::query()
            ->with([
                'catalogGroup',
                'parent',
                'children',
            ])
            ->withCount('products')
            ->find($id);

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Category
    {
        $model = CategoryModel::query()
            ->with([
                'catalogGroup',
                'parent',
                'children',
            ])
            ->withCount('products')
            ->where('slug', $slug)
            ->orWhere('full_slug', $slug)
            ->first();

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function getTree(?int $catalogGroupId = null): Collection
    {
        return CategoryModel::query()
            ->whereNull('parent_id')
            ->when($catalogGroupId, function ($query) use ($catalogGroupId) {
                $query->where('catalog_group_id', $catalogGroupId);
            })
            ->where('is_active', true)
            ->with([
                'childrenRecursive' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => CategoryMapper::toEntity($model));
    }

public function getMenuTree(?int $catalogGroupId = null): Collection
{
    return CategoryModel::query()
        ->whereNull('parent_id')
        ->when($catalogGroupId, function ($query) use ($catalogGroupId) {
            $query->where('catalog_group_id', $catalogGroupId);
        })
        ->where('is_active', true)
        ->where('is_visible_in_menu', true)
        ->with([
            'childrenRecursive' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('is_visible_in_menu', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },
        ])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->map(fn ($model) => CategoryMapper::toEntity($model));
}

    public function save(Category $category): Category
    {
        $model = $category->id()
            ? CategoryModel::find($category->id())
            : null;

        if (! $model) {
            $model = CategoryMapper::toModel($category);
        } else {
            $model->catalog_group_id = $category->catalogGroupId();
            $model->parent_id = $category->parentId();
            $model->name = $category->name();
            $model->slug = $category->slug();
            $model->full_slug = $category->fullSlug();
            $model->description = $category->description();
            $model->image_url = $category->imageUrl();
            $model->icon_url = $category->iconUrl();
            $model->cover_image_url = $category->coverImageUrl();
            $model->level = $category->level();
            $model->sort_order = $category->sortOrder();
            $model->is_active = $category->isActive();
            $model->is_visible_in_menu = $category->isVisibleInMenu();
        }

        $model->save();

        $model->load(['catalogGroup', 'parent', 'children']);
        $model->loadCount('products');

        return CategoryMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return CategoryModel::where('id', $id)->delete() > 0;
    }
}
