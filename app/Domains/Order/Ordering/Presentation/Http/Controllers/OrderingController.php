<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domains\Order\Ordering\Application\UseCases\CreateOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\CancelOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\UpdateOrderStatusUseCase;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;

class OrderingController extends Controller
{
    public function __construct(
        private CreateOrderUseCase $createOrderUseCase,
        private CancelOrderUseCase $cancelOrderUseCase,
        private UpdateOrderStatusUseCase $updateOrderStatusUseCase
    ) {}

    // 1. Checkout / Buat Order
    public function store(Request $request): JsonResponse
    {
        try {
            // Jalankan usecase
            $order = $this->createOrderUseCase->execute(
                userId: $request->input('user_id'),
                shippingAddress: $request->input('shipping_address'),
                cartItemIds: $request->input('cart_item_ids', []) // Tangkap array checkbox dari frontend
            );

            // Mapping items agar menampilkan properti di dalam array JSON
            $itemsData = array_map(function ($item) {
                return [
                    'id'          => $item->id, // Sekarang ID item sudah tidak null
                    'productId'   => $item->productId,
                    'storeId'     => $item->storeId,
                    'productName' => $item->productName,
                    'sku'          => $item->sku,
                    'price'        => $item->price,
                    'quantity'     => $item->quantity,
                ];
            }, $order->items);

            // PERBAIKAN: Pastikan 'id' => $order->id dimasukkan ke dalam response data
            return response()->json([
                'success' => true,
                'data' => [
                    'id'              => $order->id, // <--- ID ORDER UTAMA SEKARANG DI SINI
                    'orderNumber'     => $order->orderNumber,
                    'userId'          => $order->userId,
                    'totalAmount'     => $order->totalAmount,
                    'status'          => $order->status,
                    'shippingAddress' => $order->shippingAddress,
                    'items'           => $itemsData
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    // 2. Get By User ID (Menerima parameter string UUID dari URL rute)
    public function getByCustomer(string $userId): JsonResponse
    {
        $orders = OrderModel::with('items')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }

    // 3. Get By ID Toko / Store + Fitur Filter untuk Seller
    public function getByStore(Request $request, $storeId): JsonResponse
    {
        if (!is_numeric($storeId)) {
            return response()->json(['success' => false, 'message' => 'Store ID harus berupa angka.'], 400);
        }

        // Ambil query parameter untuk kebutuhan filter seller
        $statusFilter = $request->query('status'); // misal: ?status=pending
        $searchFilter = $request->query('order_number'); // misal: ?order_number=ORD-2026

        $query = OrderModel::whereHas('items', function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        });

        // Jalankan kondisional filter jika seller mengirimkan parameter filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($searchFilter) {
            $query->where('order_number', 'LIKE', '%' . $searchFilter . '%');
        }

        $orders = $query->with(['items' => function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        }])->orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }

    // 4. Get Detail Order
    public function show($id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID Order harus berupa angka.'], 400);
        }

        $order = OrderModel::with('items')->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => $order]);
    }

    // 5. Cancel Order
    public function cancel($id): JsonResponse
    {
        try {
            $this->cancelOrderUseCase->execute((int)$id);
            return response()->json(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 6. Update Status
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|string']);
        $this->updateOrderStatusUseCase->execute((int)$id, $request->status);
        return response()->json(['success' => true, 'message' => 'Status order berhasil diperbarui.']);
    }
}
