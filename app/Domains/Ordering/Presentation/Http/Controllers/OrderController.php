<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Application\DTOs\CancelOrderData;
use App\Domains\Ordering\Application\DTOs\UpdateOrderStatusData;
use App\Domains\Ordering\Application\UseCases\Order\CancelOrderUseCase;
use App\Domains\Ordering\Application\UseCases\Order\GetOrderDetailUseCase;
use App\Domains\Ordering\Application\UseCases\Order\GetOrdersUseCase;
use App\Domains\Ordering\Application\UseCases\Order\UpdateOrderStatusUseCase;
use App\Domains\Ordering\Presentation\Http\Requests\CancelOrderRequest;
use App\Domains\Ordering\Presentation\Http\Requests\UpdateOrderStatusRequest;
use App\Domains\Ordering\Presentation\Http\Resources\OrderResource;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class OrderController extends Controller
{
    public function index(Request $request, GetOrdersUseCase $useCase): JsonResponse
    {
        $filters = $request->only(['status', 'payment_status', 'user_id']);
        $perPage = (int) $request->integer('per_page', 15);

        $orders = $useCase->execute(
            authenticatedUserId: $this->currentUserId($request),
            canViewAllOrders: $this->canManageOrders($request),
            filters: array_filter($filters, static fn ($value) => $value !== null && $value !== ''),
            perPage: $perPage,
        );

        return OrderResource::collection($orders)->response();
    }

    public function show(
        Request $request,
        string $order,
        GetOrderDetailUseCase $useCase,
    ): OrderResource {
        $orderEntity = $useCase->execute(
            identifier: $order,
            authenticatedUserId: $this->currentUserId($request),
            canViewAllOrders: $this->canManageOrders($request),
        );

        return new OrderResource($orderEntity);
    }

    public function cancel(
        CancelOrderRequest $request,
        string $order,
        CancelOrderUseCase $useCase,
    ): JsonResponse {
        try {
            $orderEntity = $useCase->execute(new CancelOrderData(
                orderIdentifier: $order,
                cancelledBy: $this->currentUserId($request),
                reason: $request->validated('reason'),
                canManageAllOrders: $this->canManageOrders($request),
            ));

            return (new OrderResource($orderEntity))->response();
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        string $order,
        UpdateOrderStatusUseCase $useCase,
    ): JsonResponse {
        if (! $this->canManageOrders($request)) {
            throw new AuthorizationException('You are not allowed to update order status.');
        }

        try {
            $orderEntity = $useCase->execute(new UpdateOrderStatusData(
                orderIdentifier: $order,
                status: (string) $request->validated('status'),
                changedBy: $this->currentUserId($request),
                note: $request->validated('note'),
            ));

            return (new OrderResource($orderEntity))->response();
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    private function currentUserId(Request $request): int
    {
        return (int) $request->user()->getAuthIdentifier();
    }

    private function canManageOrders(Request $request): bool
    {
        $user = $request->user();

        return (bool) ($user->is_admin ?? false)
            || (method_exists($user, 'can') && (
                $user->can('manage-orders') || $user->can('orders.manage')
            ));
    }
}