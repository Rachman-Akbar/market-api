<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\Str;

final class CreateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(array $data): CatalogGroup
    {
        // 1. Otomatisasi pembuatan slug jika tidak diinput dari request
        $slug = $data['slug'] ?? Str::slug($data['name']);

        // 2. Instansiasi objek Entity baru (Tanpa ID karena auto-increment database)
        // Catatan: Sesuaikan constructor ini dengan property yang ada pada Entity CatalogGroup Anda
        $catalogGroup = new CatalogGroup(
            id: null,
            name: $data['name'],
            slug: $slug,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : true
        );

        // 3. Simpan state Entity baru menggunakan method save()
        return $this->repository->save($catalogGroup);
    }
}
