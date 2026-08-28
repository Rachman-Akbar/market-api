<?php

declare(strict_types=1);

namespace App\Domains\Admin\StoreContext\Application\Services;

use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Finance\Commission\Infrastructure\Persistence\Models\SellerSettlementModel;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Admin "Store Context": scope admin monitoring to a single store.
 * Every query is constrained by store_id so an admin can inspect one store
 * without cross-store data leaking.
 */
class AdminStoreContextService
{
    private const CACHE_TTL = 300;
    public function listStores(string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        return StoreModel::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->with('owner:id,name,email')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getStore(int $storeId): StoreModel
    {
        return StoreModel::with('owner:id,name,email,avatar')->findOrFail($storeId);
    }

    public function stats(int $storeId, string $period = 'monthly'): array
    {
        return Cache::remember(
            "admin_store_context_stats_{$storeId}_{$period}",
            self::CACHE_TTL,
            fn () => $this->computeStats($storeId, $period),
        );
    }

    private function computeStats(int $storeId, string $period): array
    {
        $start = $this->startDate($period);

        $subOrders = SubOrderModel::where('store_id', $storeId)
            ->where('created_at', '>=', $start)
            ->get();

        $store = StoreModel::find($storeId);

        $settlements = SellerSettlementModel::where('store_id', $storeId)
            ->where('created_at', '>=', $start)
            ->get();

        $products = ProductModel::where('store_id', $storeId)->get();

        return [
            'period' => $period,
            'start_date' => $start->toDateString(),
            'store' => $store ? ['id' => $store->id, 'name' => $store->name, 'status' => $store->status, 'is_active' => (bool) $store->is_active] : null,
            'orders' => [
                'total' => $subOrders->count(),
                'revenue' => round($subOrders->sum('total_items_price'), 2),
                'shipping' => round($subOrders->sum('shipping_cost'), 2),
                'admin_fees' => round($subOrders->sum('admin_fee'), 2),
                'seller_net' => round($subOrders->sum('seller_net'), 2),
            ],
            'settlements' => [
                'total' => $settlements->count(),
                'settled' => $settlements->where('status', 'settled')->count(),
                'pending' => $settlements->where('status', 'pending')->count(),
                'gross' => round($settlements->sum('gross_amount'), 2),
                'admin_fee' => round($settlements->sum('admin_fee'), 2),
                'net' => round($settlements->sum('net_amount'), 2),
            ],
            'products' => [
                'total' => $products->count(),
                'active' => $products->where('is_active', true)->count(),
            ],
        ];
    }

    public function orderTrend(int $storeId, string $period = 'monthly'): array
    {
        return Cache::remember(
            "admin_store_context_trend_{$storeId}_{$period}",
            self::CACHE_TTL,
            fn () => $this->computeOrderTrend($storeId, $period),
        );
    }

    private function computeOrderTrend(int $storeId, string $period): array
    {
        $start = $this->startDate($period);
        $end = now();

        $subOrders = SubOrderModel::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $byDay = $subOrders->groupBy(fn ($o) => Carbon::parse($o->created_at)->toDateString());

        $trend = [];
        $current = Carbon::parse($start);

        while ($current->lte($end)) {
            $day = $current->toDateString();
            $rows = $byDay[$day] ?? collect();
            $trend[] = [
                'date' => $day,
                'orders' => $rows->count(),
                'revenue' => round($rows->sum('total_items_price'), 2),
            ];
            $current->addDay();
        }

        return ['period' => $period, 'trend' => $trend];
    }

    public function orders(int $storeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return SubOrderModel::where('store_id', $storeId)
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->with(['parentOrder', 'items'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function products(int $storeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ProductModel::where('store_id', $storeId)
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function settlements(int $storeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return SellerSettlementModel::where('store_id', $storeId)
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    private function startDate(string $period): Carbon
    {
        return match ($period) {
            'daily' => Carbon::today(),
            'weekly' => Carbon::now()->startOfWeek(),
            'monthly' => Carbon::now()->startOfMonth(),
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }
}
