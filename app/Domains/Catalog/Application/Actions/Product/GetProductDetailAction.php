<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

class GetProductDetailAction
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(string $id)
    {
        return $this->repository->find($id);
    }
}

