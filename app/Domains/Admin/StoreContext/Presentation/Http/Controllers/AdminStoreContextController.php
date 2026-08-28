<?php

declare(strict_types=1);

namespace App\Domains\Admin\StoreContext\Presentation\Http\Controllers;

use App\Domains\Admin\StoreContext\Application\Services\AdminStoreContextService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStoreContextController extends Controller
{
    public function __construct(
        private AdminStoreContextService $service
    ) {}

    public function stores(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $paginator = $this->service->listStores($search, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(Request $request, int $storeId): JsonResponse
    {
        $store = $this->service->getStore($storeId);

        return response()->json([
            'success' => true,
            'data' => $store,
        ]);
    }

    public function stats(Request $request, int $storeId): JsonResponse
    {
        $period = (string) $request->query('period', 'monthly');

        return response()->json([
            'success' => true,
            'data' => $this->service->stats($storeId, $period),
        ]);
    }

    public function orderTrend(Request $request, int $storeId): JsonResponse
    {
        $period = (string) $request->query('period', 'monthly');

        return response()->json([
            'success' => true,
            'data' => $this->service->orderTrend($storeId, $period),
        ]);
    }

    public function orders(Request $request, int $storeId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:30'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $paginator = $this->service->orders($storeId, $validated, (int) ($validated['per_page'] ?? 20));

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function products(Request $request, int $storeId): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $paginator = $this->service->products($storeId, $validated, (int) ($validated['per_page'] ?? 20));

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function settlements(Request $request, int $storeId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:30'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $paginator = $this->service->settlements($storeId, $validated, (int) ($validated['per_page'] ?? 20));

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
