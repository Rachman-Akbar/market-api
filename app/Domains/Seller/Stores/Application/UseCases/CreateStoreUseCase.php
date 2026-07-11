<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Features\Auth\Application\DTOs\RegisterSellerDTO;
use App\Domains\Seller\Stores\Application\DTOs\StoreData;
use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class CreateStoreUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private StoreRepositoryInterface $storeRepository
    ) {}

    public function execute(string $userId, array $data, ?string $deviceName = null): StoreData
    {
        $dto = new RegisterSellerDTO(
            storeName: $data['store_name'],
            slug: Str::slug($data['store_name']),
            description: $data['description'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            city: $data['city'] ?? null,
            province: $data['province'] ?? null,
            address: $data['address'] ?? null,
            logo: $data['logo'] ?? null,
            bannerUrl: $data['banner_url'] ?? null,
            detail: $data['detail'] ?? []
        );

        $storeId = DB::transaction(function () use ($userId, $dto): int {
            $id = $this->userRepository->registerStore($userId, $dto);
            $this->userRepository->assignRoleByName($this->userRepository->findById($userId) ?? throw new RuntimeException('User tidak ditemukan.'), 'seller');
            return $id;
        });

        $store = $this->storeRepository->findById($storeId);
        if (!$store) {
            throw new RuntimeException('Toko gagal dimuat setelah registrasi.');
        }

        return StoreData::fromEntity($store);
    }
}
