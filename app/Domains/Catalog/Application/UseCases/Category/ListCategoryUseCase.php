<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

final class ListCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->paginate(
            $filters,
            $perPage
        );
    }
}