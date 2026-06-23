<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Features\Auth\Application\DTOs\RegisterSellerDTO;
use App\Domains\Seller\Stores\Application\DTOs\StoreData;
use Illuminate\Support\Str;

final readonly class CreateStoreUseCase
{
    public function __construct(
        // Inject UserRepositoryInterface karena fungsi registerStore ada di sana
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $userId, array $data, ?string $deviceName): StoreData
    {
        // 1. Bungkus data input ke dalam DTO yang diminta oleh UserRepository
        $sellerDto = new RegisterSellerDTO(
            storeName: $data['store_name'],
            slug: Str::slug($data['store_name']),
            phone: $data['phone'] ?? null,
            city: $data['city'] ?? null,
            province: $data['province'] ?? null,
            address: $data['address'] ?? null
        );

        // 2. Jalankan pembuatan toko (mengembalikan ID integer berdasarkan kode kamu)
        $storeId = $this->userRepository->registerStore($userId, $sellerDto);

        // 3. Kembalikan dalam bentuk StoreData DTO murni ke Controller
        return new StoreData(
            id: $storeId,
            userId: $userId,
            name: $sellerDto->storeName,
            slug: $sellerDto->slug,
            description: $sellerDto->address,
            logo: null,
            isActive: true,
            detail: null
        );
    }
}