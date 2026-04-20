<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

class ListProductsAction
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(array $filters = [], int $perPage = 15)
    {
        return $this->repository->paginate($filters);
    }
}