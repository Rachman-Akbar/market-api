<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\Queries;

use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class ListProductByStoreSlugQuery
{
    public function __construct(
        private StoreRepositoryInterface $repository
    ) {}
    
    public function execute(string $slug, array $filters): Collection
    {
        // Mendelegasikan pencarian produk milik toko ke repository
        return $this->repository->listProductsByStoreSlug($slug);
    }
}   