<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domains\Order\Ordering\Application\UseCases\CreateOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\CancelOrderUseCase;
use App\Domains\Order\Ordering\Application\UseCases\UpdateOrderStatusUseCase;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;

class OrderingController extends Controller
{
    public function __construct(
        private CreateOrderUseCase $createOrderUseCase,
        private CancelOrderUseCase $cancelOrderUseCase,
        private UpdateOrderStatusUseCase $updateOrderStatusUseCase,
        private OrderRepositoryInterface $orderRepository // <--- Inject repositori untuk query baca
    ) {}

    // 1. Checkout / Buat Order
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

            $order = $this->createOrderUseCase->execute(
                userId: $userId,
                shippingAddress: $request->input('shipping_address'),
                cartItemIds: $request->input('cart_item_ids', []),
                voucherCode: $request->input('voucher_code')
            );

            $itemsData = array_map(function ($item) {
                return [
                    'id'          => $item->id,
                    'productId'   => $item->productId,
                    'storeId'     => $item->storeId,
                    'productName' => $item->productName,
                    'sku'          => $item->sku,
                    'price'        => $item->price,
                    'quantity'     => $item->quantity,
                ];
            }, $order->items);

            return response()->json([
                'success' => true,
                'data' => [
                    'id'              => $order->id,
                    'orderNumber'     => $order->orderNumber,
                    'userId'          => $order->userId,
                    'status'          => $order->status,
                    'shippingAddress' => $order->shippingAddress,
                    'items'           => $itemsData,
                    'voucherId'       => $order->voucherId,
                    'totalAmount'     => $order->totalAmount,
                    'discountAmount'  => $order->discountAmount,
                    'finalPay'        => $order->totalAmount - $order->discountAmount
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422); // Gunakan 422 untuk validasi bisnis/voucher gagal
        }
    }

    // 2. Get By User ID (Menggunakan Pola Abstraksi Repositori)
    public function getByCustomer(string $userId): JsonResponse
    {
        // Dialirkan melalui repositori yang mengembalikan entitas ter-mapping murni
        $orders = $this->orderRepository->getByUserId($userId);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    // 3. Get Detail Order
    public function show($id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID Order harus berupa angka.'], 400);
        }

        $order = $this->orderRepository->findById((int) $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    // 4. Get By ID Toko / Store
    // (Bisa tetap menggunakan OrderModel jika kriteria filter terlalu dinamis bagi repositori standar Anda)
    public function getByStore(Request $request, $storeId): JsonResponse
    {
        if (!is_numeric($storeId)) {
            return response()->json(['success' => false, 'message' => 'Store ID harus berupa angka.'], 400);
        }

        $statusFilter = $request->query('status');
        $searchFilter = $request->query('order_number');

        // Karena query antar domain (Item -> Store), diizinkan memakai data infrastruktur lokal
        $query = \App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel::whereHas('items', function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        });

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
        try {
            $this->updateOrderStatusUseCase->execute((int)$id, $request->status);
            return response()->json(['success' => true, 'message' => 'Status order berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
