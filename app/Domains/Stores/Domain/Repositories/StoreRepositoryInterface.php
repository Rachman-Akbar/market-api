<?php

namespace App\Domains\Stores\Domain\Repositories;

use App\Domains\Stores\Domain\Entities\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StoreRepositoryInterface
{
    public function all(): array;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Store;

    public function create(Store $store): Store;

    public function listStores(array $filters = []);

    public function findBySlug(string $slug);

    public function listProductsByStoreSlug(string $slug);
}
