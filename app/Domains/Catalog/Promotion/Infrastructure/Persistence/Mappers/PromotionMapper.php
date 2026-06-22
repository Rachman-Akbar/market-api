<?php

namespace App\Domains\Catalog\Promotion\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Promotion\Domain\Entities\Promotion as PromotionEntity;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel;

class PromotionMapper
{
    public static function toEntity(PromotionModel $model): PromotionEntity
    {
        return new PromotionEntity(
            id: $model->id,
            imageUrl: $model->image_url,
            mobileImageUrl: $model->mobile_image_url,
            clickAction: $model->click_action,
            targetId: $model->target_id,
            targetUrl: $model->target_url,
            sortOrder: $model->sort_order,
            isActive: $model->is_active
        );
    }
}
