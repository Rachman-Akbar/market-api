<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;
use Illuminate\Support\Facades\Cache;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    private const CACHE_TTL = 600;
 
    public function getAll(array $filters = []): Collection
{
    $query = CategoryModel::query()
        ->with(['catalogGroup', 'parent'])
        ->withCount('products');

    $this->applyFilters($query, $filters);

    return $query
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->map(
            fn($model) => CategoryMapper::toEntity($model)
        );
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

        // Tambahkan filter lain sesuai kebutuhan...
    }

    // Method lain (findById, findBySlug, save, delete, getTree) bisa disederhanakan serupa...

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
            ->where('slug', $slug)
            ->orWhere('full_slug', $slug)
            ->first();

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    // public function getTree(?int $catalogGroupId = null): Collection
    // {
    //     return CategoryModel::query()
    //         ->whereNull('parent_id')
    //         ->when($catalogGroupId, function ($query) use ($catalogGroupId) {
    //             $query->where('catalog_group_id', $catalogGroupId);
    //         })
    //         ->where('is_active', true)
    //         ->with([
    //             'childrenRecursive' => function ($query) {
    //                 $query
    //                     ->where('is_active', true)
    //                     ->orderBy('sort_order')
    //                     ->orderBy('name');
    //             },
    //         ])
    //         ->orderBy('sort_order')
    //         ->orderBy('name')
    //         ->get()
    //         ->map(fn ($model) => CategoryMapper::toEntity($model));
    // }

public function getMenuTree(?int $catalogGroupId = null): Collection
{
    $cacheKey = $catalogGroupId
        ? "category_menu_tree_{$catalogGroupId}_v1"
        : "category_menu_tree_all_v1";

    $cached = Cache::get($cacheKey);

    if ($cached === null) {

        $fresh = $this->freshMenuTree($catalogGroupId);

        Cache::put(
            $cacheKey,
            $fresh,
            self::CACHE_TTL
        );

        $cached = $fresh;
    }

    return collect($cached)
        ->map(
            fn(array $item)
                => CategoryMapper::toEntityFromArray($item)
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
            'slug', 'image_url', 'icon_url', 'sort_order'
        ])
        ->with(['childrenRecursive' => function ($query) {
            $query
                ->where('is_active', true)
                ->where('is_visible_in_menu', true)
                ->select([
                    'id', 'parent_id', 'name', 'slug', 
                    'image_url', 'icon_url', 'sort_order'
                ])
                ->orderBy('sort_order')
                ->orderBy('name');
        }])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return $models->map(function ($model) {
        return [
            'id'                => $model->id,
            'parent_id'         => $model->parent_id,
            'catalog_group_id'  => $model->catalog_group_id,
            'name'              => $model->name,
            'slug'              => $model->slug,
            'image_url'         => $model->image_url,
            'icon_url'          => $model->icon_url,
            'sort_order'        => $model->sort_order,
            'children'          => $model->childrenRecursive->map(fn($child) => [
                'id'         => $child->id,
                'parent_id'  => $child->parent_id,
                'name'       => $child->name,
                'slug'       => $child->slug,
                'image_url'  => $child->image_url,
                'icon_url'   => $child->icon_url,
                'sort_order' => $child->sort_order,
            ])->all()
        ];
    })->all();
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
    $deleted = CategoryModel::where('id', $id)->delete() > 0;

    if ($deleted) {
        Cache::flush();
    }

    return $deleted;
}

public function getAllWithCategories(): Collection
{
    $cacheKey = 'catalog_groups_with_categories_v1';

    $cached = Cache::get($cacheKey);

    if ($cached === null) {

        $models = CatalogGroupModel::query()
            ->where('is_active', true)
            ->select([
                'id',
                'name',
                'slug',
                'image_url',
                'cover_image_url'
            ])
            ->with([
                'categories' => function ($q) {
                    $q->where('is_active', true)
                        ->where('is_visible_in_menu', true)
                        ->select([
                            'id',
                            'parent_id',
                            'catalog_group_id',
                            'name',
                            'slug',
                            'image_url',
                            'icon_url',
                            'sort_order'
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }
            ])
            ->orderBy('name')
            ->get();

        $fresh = $models->map(function ($model) {

            return [
                'id' => $model->id,
                'name' => $model->name,
                'slug' => $model->slug,
                'image_url' => $model->image_url,
                'cover_image_url' => $model->cover_image_url,

                'categories' => $model->categories
                    ->map(function ($category) {

                        return [
                            'id' => $category->id,
                            'parent_id' => $category->parent_id,
                            'catalog_group_id' => $category->catalog_group_id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'image_url' => $category->image_url,
                            'icon_url' => $category->icon_url,
                            'sort_order' => $category->sort_order,
                        ];

                    })
                    ->values()
                    ->all()
            ];

        })->values()->all();

        Cache::put(
            $cacheKey,
            $fresh,
            self::CACHE_TTL
        );

        $cached = $fresh;
    }

    return collect($cached)
        ->map(
            fn(array $item)
                => CatalogGroupMapper::toEntityFromArray($item)
        );
}

    public function create(Category $category): Category
{
    $model = CategoryMapper::toModel($category);

    $model->save();

    $model->load([
        'catalogGroup',
        'parent',
        'children'
    ]);

    $model->loadCount('products');

    Cache::flush();

    return CategoryMapper::toEntity($model);
}

public function update(int $id, array $data): Category
{
    $model = CategoryModel::findOrFail($id);

    $model->update($data);

    $model->load([
        'catalogGroup',
        'parent',
        'children'
    ]);

    $model->loadCount('products');

    Cache::flush();

    return CategoryMapper::toEntity($model);
}

public function getHeaderMenu(): Collection
{
    $cacheKey = 'header_menu_v1';

    $cached = Cache::get($cacheKey);

    if ($cached === null) {

        $models = CatalogGroupModel::query()
            ->where('is_active', true)

            ->select([
                'id',
                'name',
                'slug',
                'image_url',
                'cover_image_url'
            ])

            ->with([
                'categories' => function ($query) {

                    $query
                        ->whereNull('parent_id')
                        ->where('is_active', true)
                        ->where('is_visible_in_menu', true)

                        ->select([
                            'id',
                            'catalog_group_id',
                            'parent_id',
                            'name',
                            'slug',
                            'full_slug',
                            'image_url',
                            'icon_url',
                            'sort_order',
                            'level'
                        ])

                        ->with([
                            'childrenRecursive' => function ($childQuery) {

                                $childQuery
                                    ->where('is_active', true)
                                    ->where('is_visible_in_menu', true)

                                    ->select([
                                        'id',
                                        'catalog_group_id',
                                        'parent_id',
                                        'name',
                                        'slug',
                                        'full_slug',
                                        'image_url',
                                        'icon_url',
                                        'sort_order',
                                        'level'
                                    ])

                                    ->orderBy('sort_order')
                                    ->orderBy('name');
                            }
                        ])

                        ->orderBy('sort_order')
                        ->orderBy('name');
                }
            ])

            ->orderBy('name')
            ->get();

        $fresh = $models->map(function ($group) {

            return [

                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'image_url' => $group->image_url,
                'cover_image_url' => $group->cover_image_url,

                'categories' => $group->categories
                    ->map(fn($category) => $this->mapHeaderCategory($category))
                    ->values()
                    ->all()

            ];

        })->values()->all();

        Cache::put(
            $cacheKey,
            $fresh,
            self::CACHE_TTL
        );

        $cached = $fresh;
    }

    return collect($cached);
}

private function mapHeaderCategory($category): array
{
    return [

        'id' => $category->id,
        'catalog_group_id' => $category->catalog_group_id,
        'parent_id' => $category->parent_id,

        'name' => $category->name,

        'slug' => $category->slug,
        'full_slug' => $category->full_slug,

        'image_url' => $category->image_url,
        'icon_url' => $category->icon_url,

        'sort_order' => $category->sort_order,
        'level' => $category->level,

        'children' => collect(
            $category->childrenRecursive ?? []
        )
            ->map(fn($child) => $this->mapHeaderCategory($child))
            ->values()
            ->all(),
    ];
}

public function findByPath(
    string $path
): ?Category {

    $category = CategoryModel::query()
        ->where('full_slug', $path)
        ->where('is_active', true)
        ->withCount('products')  
        ->with(['children'])   
        ->first();

    if (! $category) {
        return null;
    }

    return CategoryMapper::toEntity(
        $category
    );
}

}

    
