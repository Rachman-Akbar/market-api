<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Promotion\Domain\Entities\Promotion;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel;

final class PromotionMapper
{
    public static function toEntity(PromotionModel $model): Promotion
    {
        return new Promotion(
            id: (int) $model->id,
            storeId: $model->store_id !== null ? (int) $model->store_id : null,
            name: (string) $model->name,
            imageUrl: (string) $model->image_url,
            mobileImageUrl: $model->mobile_image_url,
            clickAction: (string) $model->click_action,
            targetId: $model->target_id !== null ? (int) $model->target_id : null,
            targetUrl: $model->target_url,
            sortOrder: (int) $model->sort_order,
            isActive: (bool) $model->is_active,
            approvalStatus: (string) $model->approval_status,
            rejectionReason: $model->rejection_reason,
            submittedAt: $model->submitted_at?->toDateTimeString(),
            approvedAt: $model->approved_at?->toDateTimeString(),
            approvedBy: $model->approved_by,
        );
    }
}
