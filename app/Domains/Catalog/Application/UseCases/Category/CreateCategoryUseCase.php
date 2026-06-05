<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Str;

final class CreateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(array $data): Category
    {
        $parent = null;
        if (!empty($data['parent_id'])) {
            $parent = $this->repository->findById((int) $data['parent_id']);
            if (!$parent) {
                throw new \InvalidArgumentException("Parent category tidak ditemukan.");
            }
        }

        $category = Category::createNew(
            catalogGroupId: (int) ($data['catalog_group_id'] ?? 0),
            parent: $parent,
            name: $data['name'],
            slug: $data['slug'] ?? Str::slug($data['name']),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
            isVisibleInMenu: (bool) ($data['is_visible_in_menu'] ?? true),
            imageUrl: $data['image_url'] ?? null,
            iconUrl: $data['icon_url'] ?? null
        );

        // Disimpan menggunakan method save tunggal yang sesuai standar DDD
        return $this->repository->save($category);
    }
}
