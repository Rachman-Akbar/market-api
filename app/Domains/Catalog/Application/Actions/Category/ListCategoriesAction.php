<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

class ListCategoriesAction
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle()
    {
        return $this->repository->paginate();
    }
}