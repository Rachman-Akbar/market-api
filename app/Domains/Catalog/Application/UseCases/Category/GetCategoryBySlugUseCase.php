<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

final class GetCategoryBySlugUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }
}