<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

final class DeleteCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(string $id): bool
    {
        return $this->repository->delete($id);
    }
}