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

            // Validasi Input
            $request->validate([
                'address_id'     => 'required|integer',
                'cart_item_ids'  => 'required|array|min:1',
                'courier'        => 'required|string',
                'payment_method' => 'required|string'
            ]);

            $order = $this->createOrderUseCase->execute(
                userId: $userId,
                addressId: (int) $request->input('address_id'),
                cartItemIds: $request->input('cart_item_ids'),
                courier: $request->input('courier'),
                paymentMethod: $request->input('payment_method'),
                voucherCode: $request->input('voucher_code')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id'               => $order->id,
                    'orderNumber'      => $order->orderNumber,
                    'status'           => $order->status,
                    'paymentStatus'    => $order->paymentStatus,
                    'snapToken'        => $order->snapToken, // <--- Token diserahkan ke frontend
                    'totalAmount'      => $order->totalAmount,
                    'shippingCost'     => $order->shippingCost,
                    'discountAmount'   => $order->discountAmount,
                    'finalPay'         => $order->getFinalPay()
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
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
