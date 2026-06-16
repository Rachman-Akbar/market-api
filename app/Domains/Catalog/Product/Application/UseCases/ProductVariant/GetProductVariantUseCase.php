<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\ProductVariant;

use App\Domains\Catalog\Domain\Repositories\ProductVariantRepositoryInterface;

final class GetProductVariantUseCase
{
    public function __construct(
        private ProductVariantRepositoryInterface $repository
    ) {}

    public function execute(
        int $id
    ) {
        return $this->repository->findById($id);
    }
}