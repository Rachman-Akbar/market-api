<?php

declare(strict_types=1);

namespace App\Domains\Seller\Customers\Infrastructure\Persistence\Repositories;

use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Customers\Domain\Repositories\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator
    {
        $summary = DB::table('orders')
            ->join('sub_orders', 'sub_orders.order_id', '=', 'orders.id')
            ->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as orders_count, SUM(sub_orders.total_items_price + sub_orders.shipping_cost) as total_spent, MAX(orders.created_at) as last_order_at')
            ->when($storeId !== null, fn ($query) => $query->where('sub_orders.store_id', $storeId))
            ->whereNotIn('orders.status', ['cancelled'])
            ->groupBy('orders.user_id');

        return User::query()
            ->joinSub($summary, 'customer_summary', fn ($join) => $join->on('customer_summary.user_id', '=', 'users.id'))
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                'users.is_active',
                'users.created_at',
                'customer_summary.orders_count',
                'customer_summary.total_spent',
                'customer_summary.last_order_at',
            ])
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);
                $query->where(fn (Builder $query) => $query->where('users.name', 'like', "%{$search}%")->orWhere('users.email', 'like', "%{$search}%"));
            })
            ->when(! empty($filters['min_orders']), fn (Builder $query) => $query->where('customer_summary.orders_count', '>=', (int) $filters['min_orders']))
            ->orderByDesc('customer_summary.last_order_at')
            ->paginate($perPage);
    }
}
