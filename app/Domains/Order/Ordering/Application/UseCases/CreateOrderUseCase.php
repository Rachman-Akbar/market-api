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

    // Mengubah parameter $userId menjadi strict string (wajib diisi)
    public function execute(string $userId, ?string $shippingAddress, array $cartItemIds = []): Order
    {
        // 1. Validasi Keamanan: Pastikan user ID benar-benar ada (tidak kosong)
        if (empty(trim($userId))) {
            throw new Exception("Sesi Anda telah berakhir. Silakan login kembali.");
        }

        // Validasi jika frontend tidak mengirimkan checkbox sama sekali
        if (empty($cartItemIds)) {
            throw new Exception("Pilih minimal satu produk untuk melakukan checkout.");
        }

        // Logika Alamat berdasarkan real user ID
        if (empty(trim($shippingAddress ?? ''))) {
            $addressRow = DB::table('addresses')->where('user_id', $userId)->first();
            if (!$addressRow) {
                throw new Exception("Alamat pengiriman gagal dimuat. Sediakan alamat manual atau tambahkan alamat di profil Anda.");
            }
            $shippingAddress = $addressRow->full_address ?? $addressRow->address ?? $addressRow->alamat;
        }

        // 2. Query Keranjang Belanja milik real user yang sedang login
        $cart = CartModel::where('user_id', $userId)->first();
        if (!$cart) {
            throw new Exception("Keranjang belanja tidak ditemukan.");
        }

        // Ambil items yang hanya dicentang oleh user
        $selectedItems = $cart->items()->whereIn('id', $cartItemIds)->get();
        if ($selectedItems->isEmpty()) {
            throw new Exception("Item yang dipilih tidak ditemukan di keranjang belanja Anda.");
        }

        $totalAmount = 0;
        $domainItems = [];

        foreach ($selectedItems as $cartItem) {
            if (is_null($cartItem->product_variant_id)) {
                throw new Exception("Data keranjang rusak.");
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
        $order = new Order(null, $orderNumber, $userId, $totalAmount, 'pending', $shippingAddress, $domainItems);

        return DB::transaction(function () use ($order, $cart, $cartItemIds) {
            $createdOrder = $this->orderRepository->create($order);

            // Hapus hanya item yang di-order, item lain yang tidak dicentang tetap tinggal di keranjang
            $cart->items()->whereIn('id', $cartItemIds)->delete();

            return $createdOrder;
        });
    }
}
