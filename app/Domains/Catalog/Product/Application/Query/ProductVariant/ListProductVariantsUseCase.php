<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\Query\ProductVariant;

use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;

final class ListProductVariantsQuery
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
