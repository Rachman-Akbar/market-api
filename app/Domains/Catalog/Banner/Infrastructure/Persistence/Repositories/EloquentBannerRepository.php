<?php

namespace App\Domains\Catalog\Banner\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Banner\Domain\Entities\Banner;
use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Mappers\BannerMapper;

class EloquentBannerRepository implements BannerRepositoryInterface
{
    public function getByStoreId(int $storeId): array
    {
        $models = BannerModel::where('store_id', $storeId)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $models->map(fn($model) => BannerMapper::toEntity($model))->all();
    }

    public function findById(int $id): ?Banner
    {
        $model = BannerModel::find($id);
        return $model ? BannerMapper::toEntity($model) : null;
    }

    public function save(Banner $banner): Banner
    {
        $data = [
            'store_id'   => $banner->storeId,
            'image_url'  => $banner->imageUrl,
            'sort_order' => $banner->sortOrder,
            'is_active'  => $banner->isActive,
        ];

        if ($banner->id) {
            $model = BannerModel::findOrFail($banner->id);
            $model->update($data);
        } else {
            $model = BannerModel::create($data);
        }

        return BannerMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = BannerModel::find($id);
        return $model ? (bool) $model->delete() : false;
    }
}
