<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Category;

final class GetCategoryByPathUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function execute(string $path): ?Category
    {
        return $this->categoryRepository->findByPath($path);
    }
}