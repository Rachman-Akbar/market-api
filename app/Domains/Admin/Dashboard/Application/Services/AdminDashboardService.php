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

        $orders = OrderModel::where('created_at', '>=', $startDate)->get();

        $totalRevenue = $orders->where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = $orders->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();
        $completedOrders = $orders->where('status', 'completed')->count();

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

        $orders = OrderModel::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        $daily = $orders->groupBy(fn ($o) => Carbon::parse($o->created_at)->toDateString());

        $trend = [];
        $current = Carbon::parse($startDate);

        while ($current->lte($endDate)) {
            $dayStr = $current->toDateString();
            $dayOrders = $daily[$dayStr] ?? collect();

            $trend[] = [
                'date' => $dayStr,
                'orders' => $dayOrders->count(),
                'revenue' => round($dayOrders->where('status', '!=', 'cancelled')->sum('total_amount'), 2),
                'completed' => $dayOrders->where('status', 'completed')->count(),
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

        $storeStats = SubOrderModel::where('created_at', '>=', $startDate)
            ->select('store_id', \DB::raw('COUNT(*) as order_count'), \DB::raw('SUM(shipping_cost + total_items_price) as revenue'))
            ->groupBy('store_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $storeStats->map(function ($stat) {
            $store = StoreModel::find($stat->store_id);
            return [
                'store_id' => $stat->store_id,
                'store_name' => $store?->name ?? 'Unknown',
                'order_count' => $stat->order_count,
                'revenue' => round((float) $stat->revenue, 2),
            ];
        })->all();
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
