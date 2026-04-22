<?php

namespace App\Domains\Catalog\Domain\Repositories;

use App\Domains\Catalog\Domain\Entities\Store;
use Illuminate\Pagination\LengthAwarePaginator;

interface StoreRepositoryInterface
{
    public function all();

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): ?Store;

    public function create(Store $store): Store;

}