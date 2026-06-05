<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;
use Illuminate\Support\Facades\Cache;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    private const CACHE_TTL = 600;

    public function getAll(array $filters = []): Collection
    {
        $query = CategoryModel::query()
            ->withCount('products');

        $this->applyFilters($query, $filters);

        // 1. Ambil semua data flat terlebih dahulu dengan sorting yang benar
        $flatCategories = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($model) => CategoryMapper::toEntity($model));

        // 2. Bangun Tree Structure secara dinamis dari data flat untuk menghindari looping DB berulang
        return $this->buildCategoryTree($flatCategories);
    }

    private function buildCategoryTree(Collection $categories): Collection
    {
        // Kelompokkan kategori berdasarkan parent_id
        $grouped = $categories->groupBy(fn($cat) => $cat->parentId() ?? 'root');

        // Fungsi rekursif untuk menyisipkan children ke dalam entitas Domain
        $itemChanger = function($items) use (&$itemChanger, $grouped) {
            return collect($items)->map(function(Category $category) use (&$itemChanger, $grouped) {
                $children = $grouped->get($category->id(), collect());

                if ($children->isNotEmpty()) {
                    $children = $itemChanger($children);
                }

                // Buat instance objek baru dengan children yang sudah terisi
                return new Category(
                    id: $category->id(),
                    catalogGroupId: $category->catalogGroupId(),
                    parentId: $category->parentId(),
                    name: $category->name(),
                    slug: $category->slug(),
                    fullSlug: $category->fullSlug(),
                    imageUrl: $category->imageUrl(),
                    iconUrl: $category->iconUrl(),
                    level: $category->level(),
                    sortOrder: $category->sortOrder(),
                    productsCount: $category->productsCount(),
                    isActive: $category->isActive(),
                    isVisibleInMenu: $category->isVisibleInMenu(),
                    children: $children->all() // Set children yang sudah terstruktur
                );
            });
        };

        // Mulai dari level paling atas (parent_id = null / root)
        return $itemChanger($grouped->get('root', collect()));
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['catalog_group_id'])) {
            $query->where('catalog_group_id', $filters['catalog_group_id']);
        }

        if (array_key_exists('parent_id', $filters)) {
            $filters['parent_id'] === null || $filters['parent_id'] === 'null'
                ? $query->whereNull('parent_id')
                : $query->where('parent_id', $filters['parent_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
    }

    public function findById(int $id): ?Category
    {
        $model = CategoryModel::query()
            ->with(['catalogGroup', 'parent', 'children'])
            ->withCount('products')
            ->find($id);

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Category
    {
        $model = CategoryModel::query()
            ->with(['catalogGroup', 'parent', 'children'])
            ->withCount('products')
            ->where('slug', $slug) // Dioptimalkan tanpa orWhere full_slug yang redundant
            ->first();

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function getMenuTree(?int $catalogGroupId = null): Collection
    {
        $cacheKey = $catalogGroupId
            ? "category_menu_tree_{$catalogGroupId}_v1"
            : "category_menu_tree_all_v1";

        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($catalogGroupId) {
            return $this->freshMenuTree($catalogGroupId);
        });

        return collect($cached)->map(
            fn(array $item) => CategoryMapper::toEntityFromArray($item)
        );
    }

    private function freshMenuTree(?int $catalogGroupId = null): array
    {
        $models = CategoryModel::query()
            ->whereNull('parent_id')
            ->when($catalogGroupId, fn($q) => $q->where('catalog_group_id', $catalogGroupId))
            ->where('is_active', true)
            ->where('is_visible_in_menu', true)
            ->select([
                'id', 'parent_id', 'catalog_group_id', 'name',
                'slug', 'full_slug', 'image_url', 'icon_url', 'sort_order', 'level'
            ])
            ->with(['childrenRecursive' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('is_visible_in_menu', true)
                    ->select([
                        'id', 'parent_id', 'catalog_group_id', 'name', 'slug',
                        'full_slug', 'image_url', 'icon_url', 'sort_order', 'level'
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $models->map(function ($model) {
            return [
                'id'               => $model->id,
                'parent_id'        => $model->parent_id,
                'catalog_group_id' => $model->catalog_group_id,
                'name'             => $model->name,
                'slug'             => $model->slug,
                'full_slug'        => $model->full_slug,
                'image_url'        => $model->image_url,
                'icon_url'         => $model->icon_url,
                'level'            => (int) $model->level,
                'sort_order'       => $model->sort_order,
                'children'         => $model->childrenRecursive->map(fn($child) => [
                    'id'               => $child->id,
                    'parent_id'        => $child->parent_id,
                    'catalog_group_id' => $child->catalog_group_id,
                    'name'             => $child->name,
                    'slug'             => $child->slug,
                    'full_slug'        => $child->full_slug,
                    'image_url'        => $child->image_url,
                    'icon_url'         => $child->icon_url,
                    'level'            => (int) $child->level,
                    'sort_order'       => $child->sort_order,
                    'children'         => []
                ])->all()
            ];
        })->all();
    }

    public function save(Category $category): Category
    {
        $model = $category->id() ? CategoryModel::find($category->id()) : new CategoryModel();

        if (!$model) {
            throw new \InvalidArgumentException("Model tidak ditemukan untuk ID: " . $category->id());
        }

        $model->catalog_group_id   = $category->catalogGroupId();
        $model->parent_id          = $category->parentId();
        $model->name               = $category->name();
        $model->slug               = $category->slug();
        $model->full_slug          = $category->fullSlug();
        $model->level              = $category->level();
        $model->sort_order         = $category->sortOrder();
        $model->is_active          = $category->isActive();
        $model->is_visible_in_menu = $category->isVisibleInMenu();
        $model->image_url          = $category->imageUrl();
        $model->icon_url           = $category->iconUrl();

        $model->save();

        $this->clearCache();

        return CategoryMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        $deleted = CategoryModel::where('id', $id)->delete() > 0;
        if ($deleted) {
            $this->clearCache();
        }
        return $deleted;
    }

    public function getAllWithCategories(): Collection
    {
        $cacheKey = 'catalog_groups_with_categories_v1';

        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return CatalogGroupModel::query()
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'is_active'])
                ->with([
                    'categories' => function ($q) {
                        $q->where('is_active', true)
                            ->where('is_visible_in_menu', true)
                            ->orderBy('sort_order')
                            ->orderBy('name');
                    }
                ])
                ->orderBy('name')
                ->get()
                ->map(function ($model) {
                    return [
                        'id'        => $model->id,
                        'name'      => $model->name,
                        'slug'      => $model->slug,
                        'is_active' => (bool) $model->is_active,
                        'categories'=> $model->categories->map(function ($category) {
                            return [
                                'id'                 => $category->id,
                                'parent_id'          => $category->parent_id,
                                'catalog_group_id'   => $category->catalog_group_id,
                                'name'               => $category->name,
                                'slug'               => $category->slug,
                                'full_slug'          => $category->full_slug,
                                'image_url'          => $category->image_url,
                                'icon_url'           => $category->icon_url,
                                'level'              => (int) $category->level,
                                'sort_order'         => $category->sort_order,
                                'is_active'          => (bool) $category->is_active,
                                'is_visible_in_menu' => (bool) $category->is_visible_in_menu,
                                'children'           => []
                            ];
                        })->values()->all()
                    ];
                })->values()->all();
        });

        return collect($cached)->map(
            fn(array $item) => CatalogGroupMapper::toEntityFromArray($item)
        );
    }

    public function getHeaderMenu(): Collection
    {
        $cacheKey = 'header_menu_v1';

        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return CatalogGroupModel::query()
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'is_active'])
                ->with([
                    'categories' => function ($query) {
                        $query->whereNull('parent_id')
                            ->where('is_active', true)
                            ->where('is_visible_in_menu', true)
                            ->with(['childrenRecursive' => function ($childQuery) {
                                $childQuery->where('is_active', true)
                                    ->where('is_visible_in_menu', true)
                                    ->orderBy('sort_order')
                                    ->orderBy('name');
                            }])
                            ->orderBy('sort_order')
                            ->orderBy('name');
                    }
                ])
                ->orderBy('name')
                ->get()
                ->map(function ($group) {
                    return [
                        'id'        => $group->id,
                        'name'      => $group->name,
                        'slug'      => $group->slug,
                        'is_active' => (bool) $group->is_active,
                        'categories'=> $group->categories
                            ->map(fn($category) => $this->mapHeaderCategory($category))
                            ->values()
                            ->all()
                    ];
                })->values()->all();
        });

        return collect($cached)->map(
            fn(array $item) => CatalogGroupMapper::toEntityFromArray($item)
        );
    }

    private function mapHeaderCategory($category): array
    {
        return [
            'id'                 => $category->id,
            'catalog_group_id'   => $category->catalog_group_id,
            'parent_id'          => $category->parent_id,
            'name'               => $category->name,
            'slug'               => $category->slug,
            'full_slug'          => $category->full_slug,
            'image_url'          => $category->image_url,
            'icon_url'           => $category->icon_url,
            'sort_order'         => $category->sort_order,
            'level'              => (int) $category->level,
            'is_active'          => (bool) $category->is_active,
            'is_visible_in_menu' => (bool) $category->is_visible_in_menu,
            'children'           => array_map(
                fn($child) => $this->mapHeaderCategory($child),
                $category->childrenRecursive ? $category->childrenRecursive->all() : []
            ),
        ];
    }

    public function findByPath(string $path): ?Category
    {
        $category = CategoryModel::query()
            ->where('full_slug', $path)
            ->where('is_active', true)
            ->withCount('products')
            ->first();

        return $category ? CategoryMapper::toEntity($category) : null;
    }

    private function clearCache(): void
    {
        Cache::forget('header_menu_v1');
        Cache::forget('catalog_groups_with_categories_v1');
        Cache::forget('category_menu_tree_all_v1');

        $groups = CatalogGroupModel::select('id')->get();
        foreach ($groups as $group) {
            Cache::forget("category_menu_tree_{$group->id}_v1");
        }
    }
}
