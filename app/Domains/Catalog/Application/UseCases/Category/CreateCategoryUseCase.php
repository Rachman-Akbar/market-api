<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Category;
use Illuminate\Support\Str;

final class CreateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(array $data): Category
    {
        $category = new Category(
            id: (string) Str::uuid(),
            entityId: (string) Str::uuid(),
            name: $data['name'],
            slug: Str::slug($data['name']),
            description: $data['description'] ?? null
        );

        return $this->repository->save($category);
    }
}