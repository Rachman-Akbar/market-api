<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Domain\Repositories;

use App\Domains\Seller\Stores\Domain\Entities\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StoreRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 8): LengthAwarePaginator;
    public function findBySlug(string $slug): ?Store;
    public function listProductsByStoreSlug(string $slug, array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?Store;

    // TAMBAHKAN INI UNTUK FUNGSI CREATE CRUD
    public function create(Store $store): Store;
    public function update(Store $store): Store;
}
