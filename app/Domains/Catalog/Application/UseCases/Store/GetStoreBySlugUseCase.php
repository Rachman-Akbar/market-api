<?php

namespace App\Domains\Catalog\Application\UseCases\Store;

use App\Domains\Catalog\Domain\Entities\Store;
use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;

final class GetStoreBySlugUseCase
{
    public function __construct(
        private readonly StoreRepositoryInterface $stores
    ) {}

    public function execute(string $slug): ?Store
    {
        return $this->stores->findBySlug($slug);
    }
}