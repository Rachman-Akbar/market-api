<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Mappers;

use App\Domains\Catalog\Domain\Entities\Store;
use App\Domains\Catalog\Domain\Entities\StoreDetail;
use App\Domains\Catalog\Infrastructure\Persistence\Models\StoreModel;

class StoreMapper
{
    public static function toEntity(StoreModel $model): Store
    {
        $detail = new StoreDetail(
            logo: null,
            description: null,
            address: null,
            latitude: null,
            longitude: null,
            phone: null
        );

        return new Store(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            isActive: (bool) $model->is_active,
            detail: $detail
        );
    }

    public static function toModel(Store $store): array
    {
        return [
            'id' => $store->id(),
            'name' => $store->name(),
            'slug' => $store->slug(),
            'is_active' => $store->isActive(),
        ];
    }
}