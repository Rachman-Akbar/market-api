<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Infrastructure\Persistence\Mappers;

use App\Domains\Seller\Stores\Domain\Entities\Store;
use App\Domains\Seller\Stores\Domain\Entities\StoreDetail;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;

final class StoreMapper
{
    public static function toEntity(StoreModel $model): Store
    {
        $detailEntity = null;

        // Jika relasi detail ada isinya, petakan semua kolom ke Entity
        if ($model->relationLoaded('detail') && $model->detail !== null) {
            $detailEntity = new StoreDetail(
                id: (int) $model->detail->id,
                storeId: (int) $model->detail->store_id,
                ownerName: $model->detail->owner_name,
                ownerPhone: $model->detail->owner_phone,
                description: $model->detail->description,
                shippingPolicy: $model->detail->shipping_policy,
                returnPolicy: $model->detail->return_policy,
                openDays: $model->detail->open_days,
                openTime: $model->detail->open_time,
                closeTime: $model->detail->close_time,
                whatsappUrl: $model->detail->whatsapp_url,
                instagramUrl: $model->detail->instagram_url,
                tiktokUrl: $model->detail->tiktok_url,
                websiteUrl: $model->detail->website_url,
                createdAt: $model->detail->created_at ? (string) $model->detail->created_at : null,
                updatedAt: $model->detail->updated_at ? (string) $model->detail->updated_at : null
            );
        }

        return new Store(
            id: (int) $model->id,
            userId: (string) $model->user_id,
            name: (string) $model->name,
            slug: (string) $model->slug,
            description: $model->description,
            shortDescription: $model->short_description,
            phone: $model->phone,
            email: $model->email,
            city: $model->city,
            province: $model->province,
            address: $model->address,
            isActive: (bool) $model->is_active,
            logo: $model->logo,
            createdAt: $model->created_at ? (string) $model->created_at : null,
            updatedAt: $model->updated_at ? (string) $model->updated_at : null,
            detail: $detailEntity // <-- PASTIKAN DATA INI IKUT DIOPER KE ENTITY UTAMA
        );
    }
}