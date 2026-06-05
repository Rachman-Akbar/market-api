<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;

final class DeleteCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        // Cek dulu apakah datanya ada sebelum dihapus (Opsional, tapi bagus untuk validasi bisnis)
        $catalogGroup = $this->repository->findById($id);

        if (!$catalogGroup) {
            throw new \InvalidArgumentException("Catalog Group tidak ditemukan.");
        }

        // Panggil method delete yang benar
        return $this->repository->delete($id);
    }
}
