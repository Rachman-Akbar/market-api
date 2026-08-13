<?php

declare(strict_types=1);

namespace App\Domains\Seller\Customers\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator;
}
