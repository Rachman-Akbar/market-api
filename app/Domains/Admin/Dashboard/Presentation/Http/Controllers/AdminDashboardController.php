<?php

declare(strict_types=1);

namespace App\Domains\Admin\Dashboard\Presentation\Http\Controllers;

use App\Domains\Admin\Dashboard\Application\Services\AdminDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $service
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'success' => true,
            'data' => $this->service->getStats($period),
        ]);
    }

    public function orderTrend(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'success' => true,
            'data' => $this->service->getOrderTrend($period),
        ]);
    }

    public function topStores(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');
        $limit = min(50, max(1, (int) $request->query('limit', 10)));

        return response()->json([
            'success' => true,
            'data' => $this->service->getTopStores($period, $limit),
        ]);
    }
}
