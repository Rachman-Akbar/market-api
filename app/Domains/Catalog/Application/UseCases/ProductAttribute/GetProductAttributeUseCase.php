<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\ProductAttribute;

use App\Domains\Catalog\Domain\Repositories\ProductAttributeRepositoryInterface;

final class GetProductAttributeUseCase
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
