<?php

declare(strict_types=1);

namespace App\Domains\Seller\Finance\Presentation\Http\Controllers;

use App\Domains\Seller\Finance\Application\Services\SellerFinanceDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerFinanceDashboardController extends Controller
{
    public function __construct(
        private SellerFinanceDashboardService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $storeId = $request->route('storeId') ?? $request->user()->store->id ?? null;

        if (! $storeId) {
            return response()->json([
                'success' => false,
                'message' => 'Toko tidak ditemukan.',
            ], 404);
        }

        $period = $request->query('period', 'monthly');

        $dashboard = $this->service->getDashboard($storeId, $period);

        return response()->json([
            'success' => true,
            'data' => $dashboard,
        ]);
    }

    public function cashflow(Request $request): JsonResponse
    {
        $storeId = $request->route('storeId') ?? $request->user()->store->id ?? null;

        if (! $storeId) {
            return response()->json([
                'success' => false,
                'message' => 'Toko tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $cashflow = $this->service->getCashflow(
            $storeId,
            $validated['from_date'],
            $validated['to_date']
        );

        return response()->json([
            'success' => true,
            'data' => $cashflow,
        ]);
    }
}
