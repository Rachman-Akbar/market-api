<?php

namespace App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\CatalogGroup\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\CatalogGroup\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Mappers\CategoryMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class EloquentCatalogGroupRepository implements CatalogGroupRepositoryInterface
{
    private const CACHE_KEY_WITH_CATEGORIES = 'catalog_groups_active_with_categories_v6';
    private const CACHE_KEY_WITHOUT_CATEGORIES = 'catalog_groups_active_without_categories_v6';
    private const CACHE_TTL = 1800;

    public function getAll(array $filters = []): Collection
    {
        $cacheable = ! array_key_exists('is_active', $filters) || filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
        $withCategories = filter_var($filters['include_categories'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        $cacheKey = $withCategories ? self::CACHE_KEY_WITH_CATEGORIES : self::CACHE_KEY_WITHOUT_CATEGORIES;

        $cached = $cacheable
            ? Cache::remember($cacheKey, self::CACHE_TTL, fn () => $this->freshCatalogGroupsAsArray($filters))
            : $this->freshCatalogGroupsAsArray($filters);

        return collect($cached)->map(
            fn (array $item) => CatalogGroupMapper::toEntityFromArray($item)
        );
    }

    public function findById(int $id): ?CatalogGroup
    {
        $cacheKey = "catalog_group_{$id}_v6";

        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $model = $this->baseQuery(true)->find($id);

            return $model ? $this->mapGroupToArray($model) : null;
        });

        return $cached ? CatalogGroupMapper::toEntityFromArray($cached) : null;
    }

    public function findBySlug(string $slug): ?CatalogGroup
    {
        $cacheKey = "catalog_group_slug_{$slug}_v6";

        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug) {
            $model = $this->baseQuery(true)
                ->where('slug', $slug)
                ->first();

            return $model ? $this->mapGroupToArray($model) : null;
        });

        return $cached ? CatalogGroupMapper::toEntityFromArray($cached) : null;
    }

    public function getCategoriesByGroupId(int $groupId): Collection
    {
        $cacheKey = "catalog_group_{$groupId}_categories_array_v6";

        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($groupId) {
            $model = CatalogGroupModel::query()->findOrFail($groupId);

            return $this->mapCategoriesToArray(
                $model->categories()
                    ->where('is_active', true)
                    ->where('is_visible_in_menu', true)
                    ->withCount('products')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
            );
        });

        return collect($cached)->map(
            fn (array $item) => CategoryMapper::toEntityFromArray($item)
        );
    }

    public function save(CatalogGroup $catalogGroup): CatalogGroup
    {
        $model = $catalogGroup->id()
            ? CatalogGroupModel::findOrFail($catalogGroup->id())
            : new CatalogGroupModel();

        $model->name = $catalogGroup->name();
        $model->slug = $catalogGroup->slug();
        $model->is_active = $catalogGroup->isActive();
        $model->save();

        if (! $model->relationLoaded('categories')) {
            $model->setRelation('categories', collect());
        }

        $this->clearCache();

        return CatalogGroupMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        $result = CatalogGroupModel::destroy($id) > 0;

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_WITH_CATEGORIES);
        Cache::forget(self::CACHE_KEY_WITHOUT_CATEGORIES);

        $groups = CatalogGroupModel::query()->select('id', 'slug')->get();
        foreach ($groups as $group) {
            Cache::forget("catalog_group_{$group->id}_v6");
            Cache::forget("catalog_group_slug_{$group->slug}_v6");
            Cache::forget("catalog_group_{$group->id}_categories_array_v6");
        }
    }

    private function baseQuery(bool $withCategories)
    {
        $query = CatalogGroupModel::query()
            ->select(['id', 'name', 'slug', 'is_active']);

        if (! $withCategories) {
            return $query;
        }

        return $query->with([
            'categories' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('is_visible_in_menu', true)
                    ->withCount('products')
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
                        'level',
                        'is_active',
                        'is_visible_in_menu',
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },
        ]);
    }

    private function freshCatalogGroupsAsArray(array $filters = []): array
    {
        $withCategories = filter_var($filters['include_categories'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;

        $models = $this->baseQuery($withCategories)
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $filters['is_active']);
            }, function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->mapGroupToArray($model))->all();
    }

    private function mapGroupToArray(CatalogGroupModel $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'slug' => $model->slug,
            'is_active' => (bool) $model->is_active,
            'categories' => $model->relationLoaded('categories') ? $this->mapCategoriesToArray($model->categories) : [],
        ];
    }

    private function mapCategoriesToArray(Collection $categories): array
    {
        return $categories->map(fn ($cat) => [
            'id' => $cat->id,
            'catalog_group_id' => $cat->catalog_group_id,
            'parent_id' => $cat->parent_id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'full_slug' => $cat->full_slug,
            'image_url' => $cat->image_url,
            'icon_url' => $cat->icon_url,
            'level' => (int) $cat->level,
            'sort_order' => (int) $cat->sort_order,
            'products_count' => $cat->products_count ?? 0,
            'is_active' => (bool) $cat->is_active,
            'is_visible_in_menu' => (bool) $cat->is_visible_in_menu,
            'children' => [],
        ])->all();
    }
}
