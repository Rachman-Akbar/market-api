<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Banner;
use App\Domains\Catalog\Infrastructure\Persistence\Models\BannerModel;

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