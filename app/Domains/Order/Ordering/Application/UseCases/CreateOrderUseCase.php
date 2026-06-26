<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Domain\Entities\Order;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Models\CartModel;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function execute(?string $userId, ?string $shippingAddress): Order
    {
        $finalUserId = $userId ?: 'fe55a239-8462-4e8f-99e1-3755faa6507a';

        // Logika Alamat: Jika request kosong, ambil data alamat yang tersedia di DB
        if (empty(trim($shippingAddress ?? ''))) {

            // 1. Ambil baris pertama dari tabel addresses milik user ini
            $addressRow = DB::table('addresses')
                ->where('user_id', $finalUserId)
                ->first();

            if (!$addressRow) {
                throw new Exception("Alamat pengiriman gagal dimuat. Sediakan alamat manual atau tambahkan alamat di profil Anda.");
            }

            // 2. Deteksi nama kolom secara otomatis agar tidak memicu error 'Column not found' lagi
            // Kita cek apakah kolomnya bernama 'full_address', 'address', atau 'alamat'
            if (isset($addressRow->full_address)) {
                $shippingAddress = $addressRow->full_address;
            } elseif (isset($addressRow->address)) {
                $shippingAddress = $addressRow->address;
            } elseif (isset($addressRow->alamat)) {
                $shippingAddress = $addressRow->alamat;
            } else {
                throw new Exception("Kolom alamat pada tabel database tidak dikenali.");
            }
        }

        $cart = CartModel::with('items')->where('user_id', $finalUserId)->first();
        if (!$cart || $cart->items->isEmpty()) {
            throw new Exception("Keranjang belanja kosong.");
        }

        $totalAmount = 0;
        $domainItems = [];

        foreach ($cart->items as $cartItem) {
            if (is_null($cartItem->product_variant_id)) {
                throw new Exception("Data keranjang rusak: Ditemukan item tanpa Product Variant ID.");
            }

            $mockPrice = 100000.00;
            $mockName = "Produk Varian ID " . $cartItem->product_variant_id;
            $mockSku = "SKU-VAR-" . $cartItem->product_variant_id;
            $mockStoreId = ($cartItem->product_variant_id == 1) ? 10 : 20;

            $totalAmount += ($mockPrice * $cartItem->quantity);

            $domainItems[] = new OrderItem(
                id: null,
                productId: (int) $cartItem->product_variant_id,
                storeId: $mockStoreId,
                productName: $mockName,
                sku: $mockSku,
                price: $mockPrice,
                quantity: $cartItem->quantity
            );
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $order = new Order(null, $orderNumber, $finalUserId, $totalAmount, 'pending', $shippingAddress, $domainItems);

        return DB::transaction(function () use ($order, $cart) {
            $createdOrder = $this->orderRepository->create($order);
            $cart->items()->delete();
            return $createdOrder;
        });
    }
}
