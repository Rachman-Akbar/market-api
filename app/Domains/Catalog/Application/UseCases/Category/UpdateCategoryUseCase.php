<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Str;

final class UpdateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data): Category
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            throw new \InvalidArgumentException("Kategori tidak ditemukan.");
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Cek jika ada perubahan parent_id
        $newParent = null;
        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== $category->parentId()) {
            if ($data['parent_id'] === $category->id()) {
                throw new \InvalidArgumentException("Kategori tidak bisa menjadi parent dari dirinya sendiri.");
            }
            if (!empty($data['parent_id'])) {
                $newParent = $this->repository->findById((int) $data['parent_id']);
                if (!$newParent) {
                    throw new \InvalidArgumentException("Parent category baru tidak ditemukan.");
                }
            }
        }

        // Update entitas utama
        $category->updateData($data, $newParent);
        $updatedCategory = $this->repository->save($category);

        // EFEK DOMINO: Jika slug atau parent berubah, update semua sub-kategori di bawahnya
        if ($newParent !== null || array_key_exists('slug', $data)) {
            $this->updateChildrenHierarchies($updatedCategory);
        }

        return $updatedCategory;
    }

    /**
     * Fungsi rekursif untuk menyelaraskan level, full_slug, dan catalog_group_id milik children
     */
    private function updateChildrenHierarchies(Category $parent): void
    {
        // Ambil data flat children langsung dari database menggunakan filter parent_id
        $filters = ['parent_id' => $parent->id()];

        // Menggunakan query langsung via Model/Repository untuk menghindari loop tree
        $childrenModels = \App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel::where('parent_id', $parent->id())->get();

        foreach ($childrenModels as $model) {
            $child = \App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper::toEntity($model);

            // Paksa update data dengan parent yang sudah ter-update
            $child->updateData([], $parent);
            $this->repository->save($child);

            // Rekursif ke level berikutnya (Level 3, 4, dst)
            $this->updateChildrenHierarchies($child);
        }
    }
}
