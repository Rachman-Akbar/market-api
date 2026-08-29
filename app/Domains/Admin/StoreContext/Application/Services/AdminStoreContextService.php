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

        $orderRow = SubOrderModel::where('store_id', $storeId)
            ->where('created_at', '>=', $start)
            ->selectRaw(
                "COUNT(*) as total, COALESCE(SUM(total_items_price), 0) as revenue, " .
                "COALESCE(SUM(shipping_cost), 0) as shipping, COALESCE(SUM(admin_fee), 0) as admin_fees, " .
                "COALESCE(SUM(seller_net), 0) as seller_net"
            )
            ->first();

        $store = StoreModel::find($storeId);

        $settlements = SellerSettlementModel::where('store_id', $storeId)
            ->where('created_at', '>=', $start)
            ->selectRaw(
                "COUNT(*) as total, COALESCE(SUM(gross_amount), 0) as gross, " .
                "COALESCE(SUM(admin_fee), 0) as admin_fee, COALESCE(SUM(net_amount), 0) as net"
            )
            ->selectRaw("SUM(CASE WHEN status = 'settled' THEN 1 ELSE 0 END) as settled")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();

        $productRow = ProductModel::where('store_id', $storeId)
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active")
            ->first();

        return [
            'period' => $period,
            'start_date' => $start->toDateString(),
            'store' => $store ? ['id' => $store->id, 'name' => $store->name, 'status' => $store->status, 'is_active' => (bool) $store->is_active] : null,
            'orders' => [
                'total' => (int) ($orderRow->total ?? 0),
                'revenue' => round((float) $orderRow->revenue, 2),
                'shipping' => round((float) $orderRow->shipping, 2),
                'admin_fees' => round((float) $orderRow->admin_fees, 2),
                'seller_net' => round((float) $orderRow->seller_net, 2),
            ],
            'settlements' => [
                'total' => (int) ($settlements->total ?? 0),
                'settled' => (int) ($settlements->settled ?? 0),
                'pending' => (int) ($settlements->pending ?? 0),
                'gross' => round((float) $settlements->gross, 2),
                'admin_fee' => round((float) $settlements->admin_fee, 2),
                'net' => round((float) $settlements->net, 2),
            ],
            'products' => [
                'total' => (int) ($productRow->total ?? 0),
                'active' => (int) ($productRow->active ?? 0),
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

        $rows = SubOrderModel::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE(created_at) as day, COUNT(*) as orders, COALESCE(SUM(total_items_price), 0) as revenue")
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $trend = [];
        $current = Carbon::parse($start);

        while ($current->lte($end)) {
            $day = $current->toDateString();
            $row = $rows[$day] ?? null;
            $trend[] = [
                'date' => $day,
                'orders' => (int) ($row->orders ?? 0),
                'revenue' => round((float) ($row->revenue ?? 0), 2),
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
