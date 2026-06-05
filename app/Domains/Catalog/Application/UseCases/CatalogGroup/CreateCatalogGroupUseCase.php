<?php

namespace App\Domains\Catalog\Application\UseCases\CatalogGroup;

use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;
use Illuminate\Support\Str;

final class CreateCatalogGroupUseCase
{
    public function __construct(
        private CatalogGroupRepositoryInterface $repository
    ) {}

    public function execute(array $data): CatalogGroup
    {
        $group = new CatalogGroup(
            id: null,
            name: $data['name'],
            slug: $data['slug'] ?? Str::slug($data['name']),
            isActive: (bool) ($data['is_active'] ?? true), // Ambil is_active dari request dan konversi ke bool
            categories: [] // Inisialisasi dengan array kosong karena data baru
        );

        return $this->repository->create($group);
    }
}
