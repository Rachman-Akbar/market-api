<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\Queries;

use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use Illuminate\Support\Collection;

final class ListProductByStoreSlugQuery
{
    private StoreRepositoryInterface $storeRepository;

    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function execute(string $slug, array $filters = []): Collection
    {
        return $this->storeRepository->listProductsByStoreSlug($slug, $filters);
    }
}
