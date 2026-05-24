<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

final class ListCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(
        array $filters = []
    ): Collection {

        return $this->repository->getAll(
            $filters
        );
    }
}