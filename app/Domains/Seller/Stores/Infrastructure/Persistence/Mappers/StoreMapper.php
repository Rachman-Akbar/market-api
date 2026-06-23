<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Infrastructure\Persistence\Mappers;

use App\Domains\Seller\Stores\Domain\Entities\Store as StoreEntity;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;

final class StoreMapper
{
    // Perbaiki urutan: public static function
    public static function toEntity(StoreModel $model): StoreEntity
    {
        return new StoreEntity(
            id: (int) $model->id,
            userId: (string) $model->user_id,
            name: (string) $model->name,
            slug: (string) $model->slug,
            description: $model->description,
            logo: $model->logo,
            isActive: (bool) $model->is_active,
            createdAt: (string) $model->created_at,
            updatedAt: (string) $model->updated_at
        );
    }

    // Perbaiki urutan: public static function
    public static function toModel(StoreEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'user_id' => $entity->userId(),
            'name' => $entity->name(),
            'slug' => $entity->slug(),
            'description' => $entity->description(),
            'logo' => $entity->logo(),
            'is_active' => $entity->isActive() ? 1 : 0,
        ];
    }
}