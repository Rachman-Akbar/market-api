<?php

declare(strict_types=1);

namespace App\Domains\Seller\Finance\Application\Services;

use App\Domains\Finance\Commission\Application\Services\SellerSettlementService;
use App\Domains\Finance\Commission\Application\Services\SellerWithdrawalService;
use App\Domains\Seller\Finance\Infrastructure\Persistence\Models\FinancialTransactionModel;
use Carbon\Carbon;

class SellerFinanceDashboardService
{
    public function __construct(
        private SellerSettlementService $settlementService,
        private SellerWithdrawalService $withdrawalService
    ) {}

    public function getDashboard(int $storeId, ?string $period = null): array
    {
        $period = $period ?? 'monthly';
        $startDate = $this->getStartDate($period);

        $transactions = FinancialTransactionModel::where('store_id', $storeId)
            ->where('occurred_at', '>=', $startDate)
            ->where('is_active', true)
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');
        $payable = $transactions->where('type', 'payable')->sum('amount');
        $receivable = $transactions->where('type', 'receivable')->sum('amount');
        $payablePaid = $transactions->where('type', 'payable')->sum('paid_amount');
        $receivablePaid = $transactions->where('type', 'receivable')->sum('paid_amount');

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'summary' => [
                'income' => round($income, 2),
                'expense' => round($expense, 2),
                'profit' => round($income - $expense, 2),
                'payable_total' => round($payable, 2),
                'payable_paid' => round($payablePaid, 2),
                'payable_remaining' => round($payable - $payablePaid, 2),
                'receivable_total' => round($receivable, 2),
                'receivable_paid' => round($receivablePaid, 2),
                'receivable_remaining' => round($receivable - $receivablePaid, 2),
            ],
            'settlement' => [
                'balance' => $this->settlementService->getStoreBalance($storeId),
                'total_withdrawn' => $this->withdrawalService->getTotalWithdrawn($storeId),
            ],
            'recent_transactions' => $transactions->sortByDesc('occurred_at')->take(10)->map(fn ($t) => [
                'id' => $t->id,
                'reference_number' => $t->reference_number,
                'type' => $t->type,
                'title' => $t->title,
                'amount' => $t->amount,
                'status' => $t->status,
                'occurred_at' => $t->occurred_at,
            ])->values()->all(),
            'daily_cashflow' => $this->getDailyCashflow($storeId, $startDate),
        ];
    }

    public function getCashflow(int $storeId, string $fromDate, string $toDate): array
    {
        $transactions = FinancialTransactionModel::where('store_id', $storeId)
            ->where('occurred_at', '>=', $fromDate)
            ->where('occurred_at', '<=', $toDate)
            ->where('is_active', true)
            ->orderBy('occurred_at')
            ->get();

        $dailyData = [];
        $current = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);

        while ($current->lte($end)) {
            $dayStr = $current->toDateString();
            $dayTransactions = $transactions->filter(fn ($t) => Carbon::parse($t->occurred_at)->toDateString() === $dayStr);

            $dailyData[] = [
                'date' => $dayStr,
                'income' => round($dayTransactions->where('type', 'income')->sum('amount'), 2),
                'expense' => round($dayTransactions->where('type', 'expense')->sum('amount'), 2),
                'payable' => round($dayTransactions->where('type', 'payable')->sum('amount'), 2),
                'receivable' => round($dayTransactions->where('type', 'receivable')->sum('amount'), 2),
            ];

            $current->addDay();
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'daily' => $dailyData,
            'totals' => [
                'income' => round($transactions->where('type', 'income')->sum('amount'), 2),
                'expense' => round($transactions->where('type', 'expense')->sum('amount'), 2),
                'payable' => round($transactions->where('type', 'payable')->sum('amount'), 2),
                'receivable' => round($transactions->where('type', 'receivable')->sum('amount'), 2),
            ],
        ];
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

    private function getDailyCashflow(int $storeId, Carbon $startDate): array
    {
        $transactions = FinancialTransactionModel::where('store_id', $storeId)
            ->where('occurred_at', '>=', $startDate)
            ->where('is_active', true)
            ->get();

        $daily = $transactions->groupBy(fn ($t) => Carbon::parse($t->occurred_at)->toDateString());

        return collect($daily)->map(fn ($dayTransactions, $date) => [
            'date' => $date,
            'income' => round($dayTransactions->where('type', 'income')->sum('amount'), 2),
            'expense' => round($dayTransactions->where('type', 'expense')->sum('amount'), 2),
        ])->sortBy('date')->values()->all();
    }
}
