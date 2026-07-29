<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\UseCases;

use App\Domains\Seller\Stores\Application\DTOs\StoreData;
use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UpdateStoreUseCase
{
    public function __construct(private StoreRepositoryInterface $storeRepository) {}

    public function execute(int $storeId, string $currentUserId, string $role, array $data): StoreData
    {
        $store = $this->storeRepository->findById($storeId);

        if (! $store) {
            throw new NotFoundHttpException("Toko dengan ID [{$storeId}] tidak ditemukan.");
        }

        if ($role !== 'admin' && $store->userId() !== $currentUserId) {
            throw new AccessDeniedHttpException('Anda tidak memiliki akses untuk mengubah toko ini.');
        }

        $store->updateDetails(
            name: $data['store_name'] ?? $store->name(),
            slug: isset($data['store_name']) ? Str::slug($data['store_name']) : $store->slug(),
            description: $data['description'] ?? $store->description(),
            shortDescription: $data['short_description'] ?? $store->shortDescription(),
            phone: $data['phone'] ?? $store->phone(),
            email: $data['email'] ?? $store->email(),
            city: $data['city'] ?? $store->city(),
            province: $data['province'] ?? $store->province(),
            address: $data['address'] ?? $store->address(),
            logo: $data['logo'] ?? $store->logo(),
            bannerUrl: $data['banner_url'] ?? $store->bannerUrl(),
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $store->isActive()
        );

        if ($role === 'admin' && isset($data['status'])) {
            $store->changeStatus((string) $data['status']);
        }

        return StoreData::fromEntity($this->storeRepository->update($store, $data['detail'] ?? null));
    }
}
