<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Store;
use App\Domains\Catalog\Domain\Entities\StoreDetail;
use App\Domains\Catalog\Infrastructure\Persistence\Models\StoreModel;

final class StoreMapper
{
    public static function toEntity(StoreModel $model): Store
    {
        $detail = null;

        if ($model->relationLoaded('detail') && $model->detail) {
            $detail = new StoreDetail(
                id: $model->detail->id,
                storeId: $model->detail->store_id,
                description: $model->detail->description,
                address: $model->detail->address,
                phone: $model->detail->phone,
            );
        }

        return new Store(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            logo: $model->logo,
            isActive: (bool) ($model->is_active ?? true),
            detail: $detail,
        );
    }

    public static function toModel(Store $store): array
    {
        return [
            'user_id' => $store->userId(),
            'name' => $store->name(),
            'slug' => $store->slug(),
            'description' => $store->description(),
            'logo' => $store->logo(),
            'is_active' => $store->isActive(),
        ];
    }
}