<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(string $id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}