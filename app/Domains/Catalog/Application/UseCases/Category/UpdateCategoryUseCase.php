<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Str;

final class UpdateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data)
    {
        $category = $this->repository->findById($id);

        if (!$category) {
            throw new \RuntimeException('Category not found');
        }

        if (isset($data['name'])) {
            $category->rename($data['name']);

            if (!isset($data['slug'])) {
                $category->changeSlug(Str::slug($data['name']));
            }
        }

        if (isset($data['slug'])) {
            $category->changeSlug($data['slug']);
        }

        return $this->repository->save($category);
    }
}