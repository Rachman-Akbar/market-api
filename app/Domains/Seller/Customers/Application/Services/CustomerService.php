<?php

declare(strict_types=1);

namespace App\Domains\Seller\Customers\Application\Services;

use App\Domains\Seller\Customers\Domain\Repositories\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class CustomerService
{
    public function __construct(private CustomerRepositoryInterface $repository) {}

    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $storeId);
    }
}
