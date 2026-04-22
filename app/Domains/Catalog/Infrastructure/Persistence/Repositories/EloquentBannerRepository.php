<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\BannerModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\BannerMapper;

class EloquentBannerRepository implements BannerRepositoryInterface
{
    public function all(): array
    {
        return BannerModel::query()
            ->where('is_active', true)
            ->get()
            ->map(fn ($model) => BannerMapper::toEntity($model))
            ->toArray();
    }
}
