<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\Str;

final class UpdateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data): CatalogGroup
    {
        // 1. Cari data lama berdasarkan ID
        $catalogGroup = $this->repository->findById($id);

        if (!$catalogGroup) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Catalog Group tidak ditemukan.");
        }

        // 2. Tentukan slug (jika nama diubah tapi slug kosong, generate otomatis)
        $slug = $data['slug'] ?? (isset($data['name']) ? Str::slug($data['name']) : $catalogGroup->slug());

        // 3. Mutasi data menggunakan method yang ada di Entity kamu
        $catalogGroup->updateData([
            'name'      => $data['name'] ?? $catalogGroup->name(),
            'slug'      => $slug,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $catalogGroup->isActive(),
        ]);

        // 4. Simpan kembali objek yang state-nya sudah berubah
        return $this->repository->save($catalogGroup);
    }
}
