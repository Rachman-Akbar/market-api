<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

class GetCategoryUseCase
{
    private $categories;
    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }
    public function execute(array $filters = [], int $perPage = 15)
    {
        return $this->categories->paginate($filters, $perPage);
    }
}
