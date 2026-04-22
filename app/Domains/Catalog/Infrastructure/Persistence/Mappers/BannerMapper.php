<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Banner;
use App\Domains\Catalog\Infrastructure\Persistence\Models\BannerModel;

class BannerMapper
{
    public static function toEntity(BannerModel $model): Banner
    {
        return new Banner(
            $model->id,
            $model->title,
            $model->image_url,
            $model->link_url,
            (bool) $model->is_active,
        );
    }
}
