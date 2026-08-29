<?php

declare(strict_types=1);

namespace App\Domains\Admin\Dashboard\Application\Services;

use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel;
use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Finance\Commission\Infrastructure\Persistence\Models\SellerSettlementModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AdminDashboardService
{
    private const CACHE_TTL = 300;

    public function getStats(string $period = 'monthly'): array
    {
        return Cache::remember("admin_dashboard_stats_{$period}", self::CACHE_TTL, function () use ($period) {
            return $this->computeStats($period);
        });
    }

    private function computeStats(string $period): array
    {
        $startDate = $this->getStartDate($period);

        $orderStats = OrderModel::where('created_at', '>=', $startDate)
            ->selectRaw("COUNT(*) as total_orders, SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders")
            ->first();

        $totalOrders = (int) ($orderStats->total_orders ?? 0);
        $completedOrders = (int) ($orderStats->completed_orders ?? 0);
        $cancelledOrders = (int) ($orderStats->cancelled_orders ?? 0);
        $totalRevenue = (float) ($orderStats->revenue ?? 0);

        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $totalStores = StoreModel::where('is_active', true)->count();
        $newStores = StoreModel::where('created_at', '>=', $startDate)->count();
        $totalProducts = ProductModel::where('is_active', true)->count();
        $newProducts = ProductModel::where('created_at', '>=', $startDate)->count();

        $totalAdminFees = SellerSettlementModel::where('created_at', '>=', $startDate)
            ->where('status', 'settled')
            ->sum('admin_fee');

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'orders' => [
                'total' => $totalOrders,
                'completed' => $completedOrders,
                'cancelled' => $cancelledOrders,
                'revenue' => round($totalRevenue, 2),
                'conversion_rate' => $totalOrders > 0
                    ? round(($completedOrders / $totalOrders) * 100, 1)
                    : 0,
            ],
            'users' => [
                'total' => $totalUsers,
                'new' => $newUsers,
            ],
            'stores' => [
                'total' => $totalStores,
                'new' => $newStores,
            ],
            'products' => [
                'total' => $totalProducts,
                'new' => $newProducts,
            ],
            'finance' => [
                'admin_fees_collected' => round((float) $totalAdminFees, 2),
            ],
        ];
    }

    public function getOrderTrend(string $period = 'monthly'): array
    {
        return Cache::remember("admin_dashboard_trend_{$period}", self::CACHE_TTL, function () use ($period) {
            return $this->computeOrderTrend($period);
        });
    }

    private function computeOrderTrend(string $period): array
    {
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $rows = OrderModel::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw("DATE(created_at) as day, COUNT(*) as orders")
            ->selectRaw("SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $trend = [];
        $current = Carbon::parse($startDate);

        while ($current->lte($endDate)) {
            $dayStr = $current->toDateString();
            $row = $rows[$dayStr] ?? null;

            $trend[] = [
                'date' => $dayStr,
                'orders' => (int) ($row->orders ?? 0),
                'revenue' => round((float) ($row->revenue ?? 0), 2),
                'completed' => (int) ($row->completed ?? 0),
            ];

            $current->addDay();
        }

        return [
            'period' => $period,
            'trend' => $trend,
        ];
    }

    public function getTopStores(string $period = 'monthly', int $limit = 10): array
    {
        return Cache::remember("admin_dashboard_top_stores_{$period}_{$limit}", self::CACHE_TTL, function () use ($period, $limit) {
            return $this->computeTopStores($period, $limit);
        });
    }

    private function computeTopStores(string $period, int $limit): array
    {
        $startDate = $this->getStartDate($period);

        $storeStats = SubOrderModel::where('sub_orders.created_at', '>=', $startDate)
            ->leftJoin('stores', 'stores.id', '=', 'sub_orders.store_id')
            ->select(
                'sub_orders.store_id',
                \DB::raw('COALESCE(stores.name, "Unknown") as store_name'),
                \DB::raw('COUNT(*) as order_count'),
                \DB::raw('SUM(shipping_cost + total_items_price) as revenue')
            )
            ->groupBy('sub_orders.store_id', 'stores.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $storeStats->map(fn ($stat) => [
            'store_id' => $stat->store_id,
            'store_name' => $stat->store_name,
            'order_count' => $stat->order_count,
            'revenue' => round((float) $stat->revenue, 2),
        ])->all();
    }

    private function getStartDate(string $period): Carbon
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
