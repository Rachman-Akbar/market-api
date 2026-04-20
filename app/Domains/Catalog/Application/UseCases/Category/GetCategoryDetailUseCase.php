<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

class GetCategoryDetailUseCase
{
    private $categories;
    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }
    public function execute($idOrSlug)
    {
        return $this->categories->findByIdOrSlug($idOrSlug);
    }
}
