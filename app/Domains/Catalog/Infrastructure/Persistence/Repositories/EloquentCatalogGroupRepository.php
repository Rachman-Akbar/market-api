<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class EloquentCatalogGroupRepository implements CatalogGroupRepositoryInterface
{
    private const CACHE_KEY = 'catalog_groups_active_v5';
    private const CACHE_TTL = 720; // 12 menit

    public function getAll(array $filters = []): Collection
    {
        $cached = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->freshCatalogGroupsAsArray();
        });

        return collect($cached)->map(
            fn(array $item) => CatalogGroupMapper::toEntityFromArray($item)
        );
    }

    private function freshCatalogGroupsAsArray(): array
    {
        $models = CatalogGroupModel::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'slug', 'is_active']) 
            ->with(['categories' => function ($query) {
                $query->where('is_active', true)
                     ->where('is_visible_in_menu', true)
                     ->select([
                         'id', 'catalog_group_id', 'parent_id', 'name', 'slug',
                         'full_slug', 'image_url', 'icon_url', 'sort_order', 'level', 'is_active', 'is_visible_in_menu'
                     ])
                     ->orderBy('sort_order')
                     ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return $models->map(function ($model) {
            return [
                'id'         => $model->id,
                'name'       => $model->name,
                'slug'       => $model->slug,
                'is_active'  => (bool) $model->is_active,
                'categories' => $model->categories->map(fn($cat) => [
                    'id'                 => $cat->id,
                    'catalog_group_id'   => $cat->catalog_group_id,
                    'parent_id'          => $cat->parent_id,
                    'name'               => $cat->name,
                    'slug'               => $cat->slug,
                    'full_slug'          => $cat->full_slug,
                    'image_url'          => $cat->image_url,
                    'icon_url'           => $cat->icon_url,
                    'level'              => (int) $cat->level,
                    'sort_order'         => $cat->sort_order,
                    'is_active'          => (bool) $cat->is_active,
                    'is_visible_in_menu' => (bool) $cat->is_visible_in_menu,
                    'children'           => []
                ])->all(),
            ];
        })->all();
    }

    public function findById(int $id): ?CatalogGroup
    {
        $cacheKey = "catalog_group_{$id}";
        $cached = Cache::get($cacheKey);

        if ($cached instanceof \__PHP_Incomplete_Class) {
            Cache::forget($cacheKey);
            $cached = null;
        }

        if ($cached === null) {
            $model = CatalogGroupModel::query()
                ->with([
                    'categories' => function ($q) {
                        $q->where('is_active', true)
                            ->where('is_visible_in_menu', true)
                            ->orderBy('sort_order')
                            ->orderBy('name');
                    }
                ])
                ->find($id);

            if (!$model) {
                return null;
            }

            $cached = [
                'id'          => $model->id,
                'name'        => $model->name,
                'slug'        => $model->slug,
                'is_active'   => (bool) $model->is_active,
                'categories'  => $model->categories->map(fn ($cat) => [
                    'id'                 => $cat->id,
                    'catalog_group_id'   => $cat->catalog_group_id,
                    'parent_id'          => $cat->parent_id,
                    'name'               => $cat->name,
                    'slug'               => $cat->slug,
                    'full_slug'          => $cat->full_slug,
                    'image_url'          => $cat->image_url,
                    'icon_url'           => $cat->icon_url,
                    'level'              => (int) $cat->level,
                    'sort_order'         => $cat->sort_order,
                    'products_count'     => $cat->products_count ?? 0,
                    'is_active'          => (bool) $cat->is_active,
                    'is_visible_in_menu' => (bool) $cat->is_visible_in_menu,
                    'children'           => [],
                ])->all(),
            ];

            Cache::put($cacheKey, $cached, 600);
        }

        return CatalogGroupMapper::toEntityFromArray($cached);
    }

    public function getCategoriesByGroupId(int $groupId): Collection
    {
        return Cache::remember("catalog_group_{$groupId}_categories", 600, function () use ($groupId) {
            $model = CatalogGroupModel::findOrFail($groupId);

            return $model->categories()
                ->where('is_active', true)
                ->where('is_visible_in_menu', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn($cat) => CategoryMapper::toEntity($cat));
        });
    }

    public function create(CatalogGroup $data): CatalogGroup
    {
        $model = CatalogGroupMapper::toModel($data);
        $model->save();
        $this->clearCache();
        return CatalogGroupMapper::toEntity($model);
    }

    public function update(int $id, array $data): CatalogGroup
    {
        $model = CatalogGroupModel::findOrFail($id);
        $model->update($data);
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
        Cache::forget(self::CACHE_KEY);

        $groups = CatalogGroupModel::select('id', 'slug')->get();

        foreach ($groups as $group) {
            Cache::forget("catalog_group_{$group->id}");
            Cache::forget("catalog_group_slug_{$group->slug}");
        }
    }

    public function findBySlug(string $slug): ?CatalogGroup
    {
        $cacheKey = "catalog_group_slug_{$slug}";
        $cached = Cache::get($cacheKey);

        if ($cached instanceof \__PHP_Incomplete_Class) {
            Cache::forget($cacheKey);
            $cached = null;
        }

        if ($cached === null) {
            $model = CatalogGroupModel::query()
                ->with([
                    'categories' => function ($q) {
                        $q->where('is_active', true)
                            ->where('is_visible_in_menu', true)
                            ->orderBy('sort_order')
                            ->orderBy('name');
                    }
                ])
                ->where('slug', $slug)
                ->first();

            if (!$model) {
                return null;
            }

            $cached = [
                'id'          => $model->id,
                'name'        => $model->name,
                'slug'        => $model->slug,
                'is_active'   => (bool) $model->is_active,
                'categories'  => $model->categories->map(fn ($cat) => [
                    'id'                 => $cat->id,
                    'catalog_group_id'   => $cat->catalog_group_id,
                    'parent_id'          => $cat->parent_id,
                    'name'               => $cat->name,
                    'slug'               => $cat->slug,
                    'full_slug'          => $cat->full_slug,
                    'image_url'          => $cat->image_url,
                    'icon_url'           => $cat->icon_url,
                    'level'              => (int) $cat->level,
                    'sort_order'         => $cat->sort_order,
                    'products_count'     => $cat->products_count ?? 0,
                    'is_active'          => (bool) $cat->is_active,
                    'is_visible_in_menu' => (bool) $cat->is_visible_in_menu,
                    'children'           => [],
                ])->all(),
            ];

            Cache::put($cacheKey, $cached, 600);
        }

        return CatalogGroupMapper::toEntityFromArray($cached);
    }
}