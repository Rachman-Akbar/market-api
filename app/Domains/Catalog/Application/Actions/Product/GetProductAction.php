<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;

class GetProductAction
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle(string $idOrSlug)
    {
        if (is_numeric($idOrSlug)) {
            return $this->repository->findById($idOrSlug);
        }

        return $this->repository->findBySlug($idOrSlug);
    }
}