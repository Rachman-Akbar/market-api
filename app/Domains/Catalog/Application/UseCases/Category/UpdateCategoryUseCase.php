<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Category;

final class UpdateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(string $id, array $data): Category
    {
        $category = $this->repository->findById($id);

        if (!$category) {
            throw new \Exception('Category not found');
        }

        if (isset($data['name'])) {
            $category->rename($data['name']);
        }

        if (isset($data['description'])) {
            $category->changeDescription($data['description']);
        }

        return $this->repository->save($category);
    }
}