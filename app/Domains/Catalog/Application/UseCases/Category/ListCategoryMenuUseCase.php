<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

final class ListCategoryMenuUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(array $filters = []): Collection
    {
        $catalogGroupId = ! empty($filters['catalog_group_id'])
            ? (int) $filters['catalog_group_id']
            : null;

        return $this->categoryRepository->getMenuTree($catalogGroupId);
    }
}
