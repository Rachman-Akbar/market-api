<?php

declare(declare_types=1);

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

        // 3. Mutasi data Entity (Menyesuaikan input atau data lama jika kosong)
        // Catatan: Jika updateDetails() kamu mengembalikan objek baru (Immutable), gunakan: $storeEntity = $storeEntity->updateDetails(...);
        $storeEntity->updateDetails(
            name: $data['store_name'] ?? $storeEntity->name(),
            slug: isset($data['store_name']) ? Str::slug($data['store_name']) : $storeEntity->slug(),
            description: $data['address'] ?? $storeEntity->description(), // Memperbaiki mapping address ke description dari kode sebelumnya
            logo: $data['logo'] ?? $storeEntity->logo(),
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : $storeEntity->isActive()
        );

        // 4. Simpan perubahan ke database melalui repositori
        $savedStore = $this->storeRepository->update($storeEntity);

        // 5. Kembalikan data dalam bentuk DTO
        return new StoreData(
            id: $savedStore->id(),
            userId: $savedStore->userId(),
            name: $savedStore->name(),
            slug: $savedStore->slug(),
            description: $savedStore->description(),
            logo: $savedStore->logo(),
            isActive: $savedStore->isActive(),
            detail: null
        );
    }
}
