<?php

namespace App\Domains\Catalog\Banner\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Mappers\BannerMapper;

final class EloquentBannerRepository implements BannerRepositoryInterface
{
    public function all(): array
    {
        return BannerModel::query()
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn ($model) => BannerMapper::toEntity($model))
            ->all();
    }
}
