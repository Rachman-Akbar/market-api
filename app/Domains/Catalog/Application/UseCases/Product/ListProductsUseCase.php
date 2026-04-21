<?php

namespace App\Domains\Catalog\Application\UseCases\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }
}
