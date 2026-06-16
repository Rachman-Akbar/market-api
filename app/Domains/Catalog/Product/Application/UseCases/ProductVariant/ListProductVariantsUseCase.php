<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Application\UseCases\ProductVariant;

use App\Domains\Catalog\Domain\Repositories\ProductVariantRepositoryInterface;

final class ListProductVariantsUseCase
{
    public function __construct(
        private ProductVariantRepositoryInterface $repository
    ) {}

    public function execute(
        int $productId
    ) {
        return $this->repository->paginate(
            $productId
        );
    }
}