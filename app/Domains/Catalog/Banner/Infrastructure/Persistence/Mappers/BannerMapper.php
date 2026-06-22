<?php

namespace App\Domains\Catalog\Banner\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Banner\Domain\Entities\Banner;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel;

class BannerMapper
{
    public static function toEntity(BannerModel $model): Banner
    {
        return new Banner(
            id: $model->id,
            storeId: $model->store_id,
            imageUrl: $model->image_url,
            sortOrder: $model->sort_order,
            isActive: $model->is_active
        );
    }
}
