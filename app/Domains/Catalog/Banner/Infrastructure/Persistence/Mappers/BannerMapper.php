<?php

namespace App\Domains\Catalog\Banner\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Banner\Domain\Entities\Banner;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel;

final class BannerMapper
{
    public static function toEntity(BannerModel $model): Banner
    {
        return new Banner(
            id: $model->id,
            title: $model->title,
            imageUrl: $model->image_url,
            linkUrl: $model->link_url,
            isActive: (bool) $model->is_active,
        );
    }
}
