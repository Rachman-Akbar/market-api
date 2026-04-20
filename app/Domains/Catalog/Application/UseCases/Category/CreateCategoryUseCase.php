<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

class CreateCategoryUseCase
{
    private CategoryRepositoryInterface $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    public function execute(array $data): Category
    {
        if ($this->categories->existsSlug($data['slug'])) {
            throw new \Exception('Slug already exists');
        }

        $category = new Category(
            null,
            $data['entity_id'],
            $data['name'],
            $data['slug'],
            $data['description'] ?? null
        );

        return $this->categories->create($category);
    }
}