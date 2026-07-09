<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domains\Order\Ordering\Application\UseCases\CreateOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\CancelOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\UpdateOrderStatusUseCase;
use App\Domains\Order\Ordering\Application\UseCases\GetOrdersUseCase; // Pastikan use case baru di-import
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Presentation\Http\Resources\OrderResource;

class OrderingController extends Controller
{
    public function __construct(
        private CreateOrderUseCase $createOrderUseCase,
        private CancelOrderUseCase $cancelOrderUseCase,
        private UpdateOrderStatusUseCase $updateOrderStatusUseCase,
        private GetOrdersUseCase $getOrdersUseCase, // Inject Use Case Pagination
        private OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * 1. Checkout / Buat Order baru (Split Order per Store)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userId = (string) $request->user()?->id;

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                ], 401);
            }

            // Validasi Input
            $request->validate([
                'address_id'     => 'required|integer',
                'cart_item_ids'  => 'required|array|min:1',
                'courier'        => 'required|string',
                'payment_method' => 'required|string'
            ]);

            // Eksekusi Use Case
            $order = $this->createOrderUseCase->execute(
                userId: $userId,
                addressId: (int) $request->input('address_id'),
                cartItemIds: (array) $request->input('cart_item_ids'),
                courier: (string) $request->input('courier'),
                paymentMethod: (string) $request->input('payment_method'),
                voucherCode: $request->input('voucher_code')
            );

            // Eksekusi Resource Presenter untuk merespons ke frontend secara rapi
            return (new OrderResource($order))
                ->additional(['success' => true])
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * 2. Ambil list order berdasarkan Customer/User ID (Mendukung Pagination & Cache)
     */
    public function getByCustomer(string $userId): JsonResponse
    {
        // Eksekusi Use Case Pagination dengan limit default 15 data per halaman
        $paginatedOrders = $this->getOrdersUseCase->execute(
            authenticatedUserId: $userId,
            canViewAllOrders: false,
            filters: [],
            perPage: 15
        );
        
        // Membungkus Paginator Domain ke dalam format JSON Resource yang valid
        return OrderResource::collection($paginatedOrders)
            ->additional(['success' => true])
            ->response();
    }

    /**
     * 3. Ambil Detail Order tunggal beserta sub-orders & items
     */
    public function show(int $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID Order harus berupa angka.'], 400);
        }

        $order = $this->orderRepository->findById((int) $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        return (new OrderResource($order))
            ->additional(['success' => true])
            ->response();
    }

    /**
     * 4. Ambil list order berdasarkan ID Toko / Store (Untuk Seller Panel)
     */
    public function getByStore(Request $request, int $storeId): JsonResponse
    {
        if (!is_numeric($storeId)) {
            return response()->json(['success' => false, 'message' => 'Store ID harus berupa angka.'], 400);
        }

        $statusFilter = $request->query('status');
        $searchFilter = $request->query('order_number');

        $query = \App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel::where('store_id', $storeId)
            ->with(['parentOrder', 'items']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($searchFilter) {
            $query->whereHas('parentOrder', function($q) use ($searchFilter) {
                $q->where('order_number', 'LIKE', '%' . $searchFilter . '%');
            });
        }

        $subOrders = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, 'data' => $subOrders]);
    }

    /**
     * 5. Batalkan Order
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $this->cancelOrderUseCase->execute($id);
            return response()->json(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * 6. Perbarui Status Logistik / Transaksi
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|string']);
        try {
            $this->updateOrderStatusUseCase->execute($id, $request->status);
            return response()->json(['success' => true, 'message' => 'Status order berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}