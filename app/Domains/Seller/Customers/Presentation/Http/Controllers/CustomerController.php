<?php

declare(strict_types=1);

namespace App\Domains\Seller\Customers\Presentation\Http\Controllers;

use App\Domains\Seller\Customers\Application\Services\CustomerService;
use App\Domains\Seller\Customers\Presentation\Http\Resources\CustomerResource;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesActiveRole;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesSellerStoreContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CustomerController extends Controller
{
    use ResolvesActiveRole;
    use ResolvesSellerStoreContext;

    public function __construct(private CustomerService $service) {}

    public function index(Request $request): JsonResponse
    {
        $storeId = $this->hasActiveRole($request, 'seller') ? $this->resolveSellerStoreId($request) : null;
        $rows = $this->service->paginate(
            $request->only(['search', 'min_orders']),
            min(100, max(1, (int) $request->query('per_page', 20))),
            $storeId
        );

        return CustomerResource::collection($rows)->additional(['success' => true])->response();
    }
}
