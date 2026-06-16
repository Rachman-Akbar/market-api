<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\ProductAttribute;

use App\Domains\Catalog\Domain\Repositories\ProductAttributeRepositoryInterface;

final class GetProductAttributeQuery
{
    public function __construct(
        private ProductAttributeRepositoryInterface $repository
    ) {}

    public function execute(
        int $id
    ) {
        return $this->repository->findById($id);
    }
}
