<?php

namespace App\Domains\Catalog\Promotion\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Promotion\Domain\Entities\Promotion;
use App\Domains\Catalog\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Mappers\PromotionMapper;

class EloquentPromotionRepository implements PromotionRepositoryInterface
{
    public function getAllActive(): array
    {
        $models = PromotionModel::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $models->map(fn($model) => PromotionMapper::toEntity($model))->all();
    }

    public function findById(int $id): ?Promotion
    {
        $model = PromotionModel::find($id);
        if (!$model) return null;

        return PromotionMapper::toEntity($model);
    }

    public function save(Promotion $promotion): Promotion
    {
        $data = [
            'image_url'        => $promotion->imageUrl,
            'mobile_image_url' => $promotion->mobileImageUrl,
            'click_action'     => $promotion->clickAction,
            'target_id'        => $promotion->targetId,
            'target_url'       => $promotion->targetUrl,
            'sort_order'       => $promotion->sortOrder,
            'is_active'        => $promotion->isActive,
        ];

        if ($promotion->id) {
            $model = PromotionModel::findOrFail($promotion->id);
            $model->update($data);
        } else {
            $model = PromotionModel::create($data);
        }

        return PromotionMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = PromotionModel::find($id);
        if ($model) {
            return (bool) $model->delete();
        }
        return false;
    }
}
