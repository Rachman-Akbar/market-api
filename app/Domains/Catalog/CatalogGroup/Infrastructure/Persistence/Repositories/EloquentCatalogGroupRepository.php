<?php

namespace App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\CatalogGroup\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\CatalogGroup\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Mappers\CatalogGroupMapper;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Mappers\CategoryMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class EloquentCatalogGroupRepository implements CatalogGroupRepositoryInterface
{
    private const CACHE_KEY_ALL = 'catalog_groups_active_v5';
    private const CACHE_TTL = 720; // 12 menit

    public function getAll(array $filters = []): Collection
    {
        $cached = Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
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
            ->with([
                'categories' => function ($query) {
                    $query->where('is_active', true)
                          ->where('is_visible_in_menu', true)
                          ->withCount('products')
                          ->select([
                              'id', 'catalog_group_id', 'parent_id', 'name', 'slug',
                              'full_slug', 'image_url', 'icon_url', 'sort_order', 'level', 'is_active', 'is_visible_in_menu'
                          ])
                          ->orderBy('sort_order')
                          ->orderBy('name');
                }
            ])
            ->orderBy('name')
            ->get();

        return $models->map(fn($model) => [
            'id'         => $model->id,
            'name'       => $model->name,
            'slug'       => $model->slug,
            'is_active'  => (bool) $model->is_active,
            'categories' => $this->mapCategoriesToArray($model->categories),
        ])->all();
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
                          ->withCount('products')
                          ->orderBy('sort_order')
                          ->orderBy('name');
                    }
                ])
                ->find($id);

            if (!$model) {
                return null;
            }

            $cached = [
                'id'         => $model->id,
                'name'       => $model->name,
                'slug'       => $model->slug,
                'is_active'  => (bool) $model->is_active,
                'categories' => $this->mapCategoriesToArray($model->categories),
            ];

            Cache::put($cacheKey, $cached, 600);
        }

        return CatalogGroupMapper::toEntityFromArray($cached);
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
                          ->withCount('products')
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
                'id'         => $model->id,
                'name'       => $model->name,
                'slug'       => $model->slug,
                'is_active'  => (bool) $model->is_active,
                'categories' => $this->mapCategoriesToArray($model->categories),
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

    /**
     * Pengganti method Create & Update (DDD Strict Standard)
     */
    public function save(CatalogGroup $catalogGroup): CatalogGroup
    {
        $model = $catalogGroup->id()
            ? CatalogGroupModel::findOrFail($catalogGroup->id())
            : new CatalogGroupModel();

        $model->name      = $catalogGroup->name();
        $model->slug      = $catalogGroup->slug();
        $model->is_active = $catalogGroup->isActive();
        $model->save();

        // Mencegah crash mapping jika data baru dibuat (belum punya relasi)
        if (!$model->relationLoaded('categories')) {
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
        Cache::forget(self::CACHE_KEY_ALL);

        $groups = CatalogGroupModel::select('id', 'slug')->get();
        foreach ($groups as $group) {
            Cache::forget("catalog_group_{$group->id}");
            Cache::forget("catalog_group_slug_{$group->slug}");
            Cache::forget("catalog_group_{$group->id}_categories");
        }
    }

    /**
     * Helper privat untuk standarisasi mapping data array kategori (Mencegah DRY)
     */
    private function mapCategoriesToArray(Collection $categories): array
    {
        return $categories->map(fn($cat) => [
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
            'children'           => []
        ])->all();
    }
}
