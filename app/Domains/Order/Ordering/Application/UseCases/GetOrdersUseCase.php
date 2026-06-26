<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\UseCases\Order;

use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class GetOrdersUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(
        string $authenticatedUserId,
        bool $canViewAllOrders = false,
        array $filters = [],
        int $perPage = 15,
    ): LengthAwarePaginator {
        $userId = $canViewAllOrders
            ? (($filters['user_id'] ?? null) ?: null)
            : $authenticatedUserId;

        return $this->orders->paginateForUser(
            userId: $userId !== null ? (string) $userId : null,
            filters: $filters,
            perPage: $perPage,
        );
    }
}
