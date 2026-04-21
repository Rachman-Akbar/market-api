<?php

namespace App\Domains\Catalog\Application\UseCases\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

final class DeleteProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(string $id): bool
    {
        return $this->repository->delete($id);
    }
}