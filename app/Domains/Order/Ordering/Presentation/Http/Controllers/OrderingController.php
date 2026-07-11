<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Controllers;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Order\Ordering\Application\UseCases\CancelOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\CreateOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\GetOrdersUseCase;
use App\Domains\Order\Ordering\Application\UseCases\GetShippingOptionsUseCase;
use App\Domains\Order\Ordering\Application\UseCases\UpdateOrderStatusUseCase;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel;
use App\Domains\Order\Ordering\Presentation\Http\Requests\CreateOrderRequest;
use App\Domains\Order\Ordering\Presentation\Http\Resources\OrderResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderingController extends Controller
{
    public function __construct(
        private CreateOrderUseCase $createOrderUseCase,
        private GetShippingOptionsUseCase $shippingOptionsUseCase,
        private CancelOrderUseCase $cancelOrderUseCase,
        private UpdateOrderStatusUseCase $updateOrderStatusUseCase,
        private GetOrdersUseCase $getOrdersUseCase,
        private OrderRepositoryInterface $orderRepository,
        private UserRepositoryInterface $userRepository
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
                voucherCode: $data['voucher_code'] ?? null
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
            filters: $request->only(['user_id', 'status', 'payment_status', 'search']),
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
            filters: $request->only(['status', 'payment_status', 'search']),
            perPage: min(100, max(1, (int) $request->query('per_page', 15)))
        );

        return OrderResource::collection($orders)->additional(['success' => true])->response();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        $role = $this->activeRole($request);
        if ($role === 'buyer' && $order->userId !== (string) $request->user()->id) {
            throw new AccessDeniedHttpException('Anda tidak dapat melihat pesanan ini.');
        }
        if ($role === 'seller') {
            $storeId = $this->sellerStoreId($request);
            if (!collect($order->subOrders)->contains(fn($subOrder) => $subOrder->storeId === $storeId)) {
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
            $query->whereHas('parentOrder', fn($builder) => $builder->where('order_number', 'like', "%{$search}%"));
        }

        $rows = $query->latest()->paginate(min(100, max(1, (int) $request->query('per_page', 15))));
        $rows->through(fn($row) => [
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
        if (!$order || ($this->activeRole($request) !== 'admin' && $order->userId !== (string) $request->user()->id)) {
            throw new AccessDeniedHttpException('Anda tidak dapat membatalkan pesanan ini.');
        }

        $this->cancelOrderUseCase->execute($id);
        return response()->json(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,shipped,completed,cancelled'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $role = $this->activeRole($request);
        if (!in_array($role, ['admin', 'seller'], true)) {
            throw new AccessDeniedHttpException('Hanya seller atau admin yang dapat memperbarui status.');
        }

        if ($role === 'seller') {
            $storeId = $this->sellerStoreId($request);
            $affected = DB::table('sub_orders')
                ->where('id', $id)
                ->where('store_id', $storeId)
                ->update([
                    'status' => $validated['status'],
                    'tracking_number' => $validated['tracking_number'] ?? null,
                    'updated_at' => now(),
                ]);
            if ($affected === 0) {
                throw new AccessDeniedHttpException('Sub-order tidak ditemukan untuk toko Anda.');
            }
            return response()->json(['success' => true, 'message' => 'Status sub-order berhasil diperbarui.']);
        }

        $this->updateOrderStatusUseCase->execute($id, $validated['status']);
        return response()->json(['success' => true, 'message' => 'Status order berhasil diperbarui.']);
    }

    private function activeRole(Request $request): string
    {
        return (string) ($this->userRepository->getActiveRoleFromCurrentToken($request->user()) ?: 'buyer');
    }

    private function sellerStoreId(Request $request): int
    {
        $user = $request->user();
        if (!$this->userRepository->hasSellerAccess($user)) {
            throw new AccessDeniedHttpException('Toko aktif tidak ditemukan.');
        }
        return (int) $user->store->id;
    }
}
