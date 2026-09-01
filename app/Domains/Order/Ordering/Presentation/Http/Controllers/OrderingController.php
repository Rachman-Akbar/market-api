<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Controllers;

use App\Domains\Engagement\Mission\Application\Services\MissionService;
use App\Domains\Identity\User\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Order\Ordering\Application\UseCases\CancelOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\CreateOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\GetOrdersUseCase;
use App\Domains\Order\Ordering\Application\UseCases\GetShippingOptionsUseCase;
use App\Domains\Order\Ordering\Application\UseCases\UpdateOrderStatusUseCase;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel;
use App\Domains\Order\Ordering\Presentation\Http\Requests\CreateOrderRequest;
use App\Domains\Order\Ordering\Presentation\Http\Resources\OrderResource;
use App\Domains\Seller\Stock\Application\Services\StockMovementService;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesSellerStoreContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderingController extends Controller
{
    use ResolvesSellerStoreContext;

    public function __construct(
        private CreateOrderUseCase $createOrderUseCase,
        private GetShippingOptionsUseCase $shippingOptionsUseCase,
        private CancelOrderUseCase $cancelOrderUseCase,
        private UpdateOrderStatusUseCase $updateOrderStatusUseCase,
        private GetOrdersUseCase $getOrdersUseCase,
        private OrderRepositoryInterface $orderRepository,
        private UserRepositoryInterface $userRepository,
        private StockMovementService $stockMovementService,
        private MissionService $missionService
    ) {}

    public function shippingOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'cart_item_ids' => ['required', 'array', 'min:1'],
            'cart_item_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $data = $this->shippingOptionsUseCase->execute(
            (string) $request->user()->id,
            (int) $validated['address_id'],
            $validated['cart_item_ids']
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $order = $this->createOrderUseCase->execute(
                userId: (string) $request->user()->id,
                addressId: isset($data['address_id']) ? (int) $data['address_id'] : null,
                cartItemIds: $data['cart_item_ids'],
                courier: (string) $data['courier'],
                service: $data['service'] ?? null,
                paymentMethod: (string) $data['payment_method'],
                voucherCode: $data['voucher_code'] ?? null,
                orderType: $data['order_type'] ?? 'normal',
                preorderReleaseAt: $data['preorder_release_at'] ?? null,
                bookingExpiresAt: $data['booking_expires_at'] ?? null
            );

            return (new OrderResource($order))
                ->additional(['success' => true, 'message' => 'Pesanan berhasil dibuat.'])
                ->response()
                ->setStatusCode(201);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $role = $this->activeRole($request);
        if ($role !== 'admin') {
            throw new AccessDeniedHttpException('Hanya admin yang dapat melihat seluruh pesanan.');
        }

        $orders = $this->getOrdersUseCase->execute(
            authenticatedUserId: (string) $request->user()->id,
            canViewAllOrders: true,
            filters: $request->only(['user_id', 'status', 'payment_status', 'order_type', 'search']),
            perPage: min(100, max(1, (int) $request->query('per_page', 15)))
        );

        return OrderResource::collection($orders)->additional(['success' => true])->response();
    }

    public function getByCustomer(Request $request, string $userId): JsonResponse
    {
        $authenticatedId = (string) $request->user()->id;
        $role = $this->activeRole($request);
        if ($role !== 'admin' && $userId !== $authenticatedId) {
            throw new AccessDeniedHttpException('Anda tidak dapat melihat pesanan pengguna lain.');
        }

        $orders = $this->getOrdersUseCase->execute(
            authenticatedUserId: $role === 'admin' ? $userId : $authenticatedId,
            canViewAllOrders: false,
            filters: $request->only(['status', 'payment_status', 'order_type', 'search']),
            perPage: min(100, max(1, (int) $request->query('per_page', 15)))
        );

        return OrderResource::collection($orders)->additional(['success' => true])->response();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        $role = $this->activeRole($request);
        if ($role === 'buyer' && $order->userId !== (string) $request->user()->id) {
            throw new AccessDeniedHttpException('Anda tidak dapat melihat pesanan ini.');
        }
        if ($role === 'seller') {
            $storeId = $this->sellerStoreId($request);
            if (! collect($order->subOrders)->contains(fn ($subOrder) => $subOrder->storeId === $storeId)) {
                throw new AccessDeniedHttpException('Pesanan ini bukan milik toko Anda.');
            }
        }

        return (new OrderResource($order))->additional(['success' => true])->response();
    }

    public function getByStore(Request $request, int $storeId): JsonResponse
    {
        $role = $this->activeRole($request);
        if ($role !== 'admin' && ($role !== 'seller' || $storeId !== $this->sellerStoreId($request))) {
            throw new AccessDeniedHttpException('Anda tidak dapat melihat pesanan toko ini.');
        }

        $query = SubOrderModel::query()
            ->where('store_id', $storeId)
            ->with(['parentOrder', 'items', 'store']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('order_number')) {
            $search = trim((string) $request->query('order_number'));
            $query->whereHas('parentOrder', fn ($builder) => $builder->where('order_number', 'like', "%{$search}%"));
        }

        $rows = $query->latest()->paginate(min(100, max(1, (int) $request->query('per_page', 15))));
        $rows->through(fn ($row) => [
            'id' => $row->id,
            'order_id' => $row->order_id,
            'order_number' => $row->parentOrder?->order_number,
            'sub_order_number' => $row->sub_order_number,
            'store_id' => $row->store_id,
            'store_name' => $row->store?->name,
            'total_items_price' => (float) $row->total_items_price,
            'shipping_cost' => (float) $row->shipping_cost,
            'courier' => $row->courier,
            'service' => $row->service,
            'status' => $row->status,
            'payment_status' => $row->parentOrder?->payment_status,
            'tracking_number' => $row->tracking_number,
            'items' => $row->items,
            'created_at' => $row->created_at?->toIso8601String(),
        ]);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);
        if (! $order || ($this->activeRole($request) !== 'admin' && $order->userId !== (string) $request->user()->id)) {
            throw new AccessDeniedHttpException('Anda tidak dapat membatalkan pesanan ini.');
        }

        $this->cancelOrderUseCase->execute($id);

        return response()->json(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,shipped,received,completed,cancelled'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $role = $this->activeRole($request);

        if ($role === 'buyer') {
            if ($validated['status'] !== 'received') {
                throw new AccessDeniedHttpException('Buyer hanya dapat mengonfirmasi pesanan diterima.');
            }

            $order = $this->orderRepository->findById($id);

            if (! $order || $order->userId !== (string) $request->user()->id) {
                throw new AccessDeniedHttpException('Pesanan tidak ditemukan untuk akun Anda.');
            }

            $this->updateOrderStatusUseCase->execute($id, 'received');

            return response()->json(['success' => true, 'message' => 'Pesanan berhasil dikonfirmasi diterima.']);
        }

        if (! in_array($role, ['admin', 'seller'], true)) {
            throw new AccessDeniedHttpException('Hanya seller atau admin yang dapat memperbarui status.');
        }

        if ($role === 'seller') {
            $storeId = $this->sellerStoreId($request);
            $result = DB::transaction(function () use ($id, $storeId, $validated): array {
                $subOrder = SubOrderModel::query()
                    ->where('id', $id)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->first();

                if (! $subOrder) {
                    throw new AccessDeniedHttpException('Sub-order tidak ditemukan untuk toko Anda.');
                }

                $previousStatus = (string) $subOrder->status;
                $nextStatus = (string) $validated['status'];
                $transitions = [
                    'pending' => ['processing', 'cancelled'],
                    'processing' => ['shipped', 'cancelled'],
                    'shipped' => ['received', 'completed'],
                    'received' => ['completed'],
                    'completed' => [],
                    'cancelled' => [],
                ];

                if ($previousStatus !== $nextStatus && ! in_array($nextStatus, $transitions[$previousStatus] ?? [], true)) {
                    throw new AccessDeniedHttpException("Perubahan status dari {$previousStatus} ke {$nextStatus} tidak diizinkan.");
                }

                if ($previousStatus === $nextStatus) {
                    return ['order_id' => (int) $subOrder->order_id, 'parent_completed' => false];
                }

                $parent = $subOrder->parentOrder()->lockForUpdate()->firstOrFail();

                if ($nextStatus === 'processing' && $parent->payment_method === 'midtrans' && $parent->payment_status !== 'paid') {
                    throw new AccessDeniedHttpException('Order Midtrans belum memiliki pembayaran yang berhasil.');
                }

                $subOrder->forceFill([
                    'status' => $nextStatus,
                    'tracking_number' => $validated['tracking_number'] ?? $subOrder->tracking_number,
                ])->save();

                $this->stockMovementService->syncSubOrderStatus((int) $subOrder->id, $previousStatus, $nextStatus);
                $statuses = SubOrderModel::query()->where('order_id', $parent->id)->pluck('status');
                $parentStatus = 'pending';

                if ($statuses->every(fn (string $status): bool => $status === 'cancelled')) {
                    $parentStatus = 'cancelled';
                } elseif ($statuses->every(fn (string $status): bool => $status === 'completed')) {
                    $parentStatus = 'completed';
                } elseif ($statuses->every(fn (string $status): bool => in_array($status, ['received', 'completed'], true))) {
                    $parentStatus = 'received';
                } elseif ($statuses->every(fn (string $status): bool => in_array($status, ['shipped', 'received', 'completed'], true))) {
                    $parentStatus = 'shipped';
                } elseif ($statuses->contains(fn (string $status): bool => in_array($status, ['processing', 'shipped', 'received', 'completed'], true))) {
                    $parentStatus = 'processing';
                }

                $previousParentStatus = (string) $parent->status;
                $parent->forceFill([
                    'status' => $parentStatus,
                    'received_at' => $parentStatus === 'received' ? ($parent->received_at ?? now()) : $parent->received_at,
                ])->save();

                $this->stockMovementService->syncOrderStatus((int) $parent->id, $previousParentStatus, $parentStatus);

                return [
                    'order_id' => (int) $parent->id,
                    'user_id' => (string) $parent->user_id,
                    'order_type' => (string) ($parent->order_type ?? 'normal'),
                    'parent_completed' => $previousParentStatus !== 'completed' && $parentStatus === 'completed',
                ];
            });

            if ($result['parent_completed']) {
                $this->missionService->recordEvent($result['user_id'], 'order_completed', 1, [
                    'order_id' => $result['order_id'],
                    'order_type' => $result['order_type'],
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Status sub-order berhasil diperbarui.']);
        }

        $this->updateOrderStatusUseCase->execute($id, $validated['status'], $validated['reason'] ?? null);

        return response()->json(['success' => true, 'message' => 'Status order berhasil diperbarui.']);
    }

    private function activeRole(Request $request): string
    {
        return (string) ($this->userRepository->getActiveRoleFromCurrentToken($request->user()) ?: 'buyer');
    }

    private function sellerStoreId(Request $request): int
    {
        if (! $this->userRepository->hasSellerAccess($request->user())) {
            throw new AccessDeniedHttpException('Toko aktif tidak ditemukan.');
        }

        return $this->resolveSellerStoreId($request);
    }
}
