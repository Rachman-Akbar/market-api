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
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached === null) {
            $cached = $this->freshCatalogGroupsAsArray();
            Cache::put(self::CACHE_KEY, $cached, self::CACHE_TTL);
        }

        // Convert array ke Entity
        return collect($cached)->map(
            fn(array $item) => CatalogGroupMapper::toEntityFromArray($item)
        );
    }

    private function freshCatalogGroupsAsArray(): array
    {
        $models = CatalogGroupModel::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'slug', 'description', 'image_url', 'cover_image_url'])
            ->with(['categories' => function ($query) {
                $query->where('is_active', true)
                      ->where('is_visible_in_menu', true)
                      ->select([
                          'id', 'catalog_group_id', 'parent_id', 'name', 'slug',
                          'image_url', 'icon_url', 'sort_order'
                      ])
                      ->orderBy('sort_order')
                      ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return $models->map(function ($model) {
            return [
                'id'              => $model->id,
                'name'            => $model->name,
                'slug'            => $model->slug,
                'description'     => $model->description,
                'image_url'       => $model->image_url,
                'cover_image_url' => $model->cover_image_url,
                'categories'      => $model->categories->map(fn($cat) => [
                    'id'         => $cat->id,
                    'parent_id'  => $cat->parent_id,
                    'name'       => $cat->name,
                    'slug'       => $cat->slug,
                    'image_url'  => $cat->image_url,
                    'icon_url'   => $cat->icon_url,
                    'sort_order' => $cat->sort_order,
                ])->all(),
            ];
        })->all();
    }

    public function findById(int $id): ?CatalogGroup
    {
        return Cache::remember("catalog_group_{$id}", 600, function () use ($id) {
            $model = CatalogGroupModel::with(['categories' => function ($q) {
                $q->where('is_active', true)
                  ->where('is_visible_in_menu', true)
                  ->orderBy('sort_order')
                  ->orderBy('name');
            }])->find($id);

            return $model ? CatalogGroupMapper::toEntity($model) : null;
        });
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
    }
}

