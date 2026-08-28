<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\IakProviderService;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoFinanceEntryModel;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PpoAdminDashboardController extends Controller
{
    private const CACHE_TTL = 300;

    public function __construct(
        private IakProviderService $provider,
    ) {}

    public function dashboard(): JsonResponse
    {
        $data = Cache::remember('ppob_admin_dashboard', self::CACHE_TTL, function () {
            $today = now()->startOfDay();

            $totals = PpoTransactionModel::whereIn('status', ['success', 'processing', 'pending'])
                ->selectRaw('
                    COUNT(*) as total_orders,
                    COALESCE(SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END), 0) as success_orders,
                    COALESCE(SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END), 0) as failed_orders,
                    COALESCE(SUM(revenue), 0) as total_revenue,
                    COALESCE(SUM(net_profit), 0) as total_profit
                ')
                ->first();

            $todayStats = PpoTransactionModel::where('created_at', '>=', $today)
                ->selectRaw('
                    COUNT(*) as orders_today,
                    COALESCE(SUM(revenue), 0) as revenue_today,
                    COALESCE(SUM(net_profit), 0) as profit_today
                ')
                ->first();

            $byCategory = PpoTransactionModel::where('status', 'success')
                ->selectRaw('category, COUNT(*) as count, COALESCE(SUM(revenue), 0) as revenue')
                ->groupBy('category')
                ->orderByDesc('count')
                ->get();

            $recent = PpoTransactionModel::with(['operator'])
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn ($tx) => [
                    'id' => $tx->id,
                    'reference_id' => $tx->reference_id,
                    'product_name' => $tx->product_name,
                    'category' => $tx->category,
                    'customer_id' => $tx->customer_id,
                    'total_amount' => (float) $tx->total_amount,
                    'status' => $tx->status,
                    'created_at' => $tx->created_at?->toDateTimeString(),
                    'operator' => $tx->operator?->name,
                ]);

            return [
                'summary' => [
                    'total_orders' => (int) $totals->total_orders,
                    'success_orders' => (int) $totals->success_orders,
                    'failed_orders' => (int) $totals->failed_orders,
                    'total_revenue' => (float) $totals->total_revenue,
                    'total_profit' => (float) $totals->total_profit,
                    'orders_today' => (int) $todayStats->orders_today,
                    'revenue_today' => (float) $todayStats->revenue_today,
                    'profit_today' => (float) $todayStats->profit_today,
                ],
                'by_category' => $byCategory,
                'recent_transactions' => $recent,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function balance(): JsonResponse
    {
        $balance = $this->provider->checkBalance();

        return response()->json([
            'success' => true,
            'data' => $balance,
        ]);
    }

    public function financeSummary(): JsonResponse
    {
        $data = Cache::remember('ppob_admin_finance', self::CACHE_TTL, function () {
            $entries = PpoFinanceEntryModel::where('status', 'posted')
                ->where('is_active', true)
                ->selectRaw('transaction_type, COALESCE(SUM(amount), 0) as total, COUNT(*) as count')
                ->groupBy('transaction_type')
                ->get()
                ->keyBy('transaction_type');

            return [
                'revenue' => (float) ($entries->get('revenue')?->total ?? 0),
                'provider_cost' => (float) ($entries->get('provider_cost')?->total ?? 0),
                'admin_fee' => (float) ($entries->get('admin_fee')?->total ?? 0),
                'commission' => (float) ($entries->get('commission')?->total ?? 0),
                'net_profit' => (float) ($entries->get('net_profit')?->total ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
