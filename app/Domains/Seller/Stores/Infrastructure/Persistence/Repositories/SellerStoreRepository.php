<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\Persistence\Repositories;

use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;

final class SellerStoreRepository
{
    public function findByUserId(int|string $userId): ?StoreModel
    {
        return StoreModel::query()
            ->where('user_id', $userId)
            ->first();
    }

    public function existsBySlug(string $slug): bool
    {
        return StoreModel::query()
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @param array{
     *     user_id:int|string,
     *     name:string,
     *     slug:string,
     *     description?:string|null,
     *     phone:string,
     *     email?:string|null,
     *     address:string,
     *     is_active:bool
     * } $data
     */
    public function createFromSellerOnboarding(array $data): StoreModel
    {
        return StoreModel::query()->create($data);
    }
}
