<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\UseCases;

use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Seller\Stores\Application\DTOs\StoreData;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class UpdateStoreUseCase
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository
    ) {}

    /**
     * Mengeksekusi pembaruan data toko atau status toko.
     */
    public function execute(int $storeId, string $currentUserId, string $role, array $data): StoreData
    {
        // 1. Cari data toko lama berdasarkan ID
        $storeEntity = $this->storeRepository->findById($storeId);

        if (! $storeEntity) {
            throw new NotFoundHttpException("Toko dengan ID [{$storeId}] tidak ditemukan.");
        }

        // 2. Validasi Hak Akses (Hanya pemilik toko atau Admin yang boleh update)
        if ($role !== 'admin' && $storeEntity->userId() !== $currentUserId) {
            throw new AccessDeniedHttpException("Anda tidak memiliki akses untuk mengubah toko ini.");
        }

        // 3. FIX: Mutasi data Entity dengan argumen lengkap sesuai struktur tabel baru
        $storeEntity->updateDetails(
            name: $data['store_name'] ?? $storeEntity->name(),
            slug: isset($data['store_name']) ? Str::slug($data['store_name']) : $storeEntity->slug(),
            description: $data['description'] ?? $storeEntity->description(),
            shortDescription: $data['short_description'] ?? $storeEntity->shortDescription(),
            phone: $data['phone'] ?? $storeEntity->phone(),
            email: $data['email'] ?? $storeEntity->email(),
            city: $data['city'] ?? $storeEntity->city(),
            province: $data['province'] ?? $storeEntity->province(),
            address: $data['address'] ?? $storeEntity->address(),
            logo: $data['logo'] ?? $storeEntity->logo(),
            bannerUrl: $data['banner_url'] ?? $storeEntity->bannerUrl(),
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : $storeEntity->isActive()
        );

        // 4. Simpan perubahan ke database melalui repositori
       $savedStore = $this->storeRepository->update($storeEntity, $data['detail'] ?? null);

        // 5. FIX: Gunakan static factory method dari DTO (`fromEntity`) agar otomatis memetakan seluruh kolom termasuk relasi detailnya jika ada
        return StoreData::fromEntity($savedStore);
    }
}