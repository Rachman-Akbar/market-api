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

    public function execute(string $userId, ?string $shippingAddress, array $cartItemIds = [], ?string $voucherCode = null): Order
    {
        // Validasi Keamanan: Pastikan user ID benar-benar ada
        if (empty(trim($userId))) {
            throw new Exception("Sesi Anda telah berakhir. Silakan login kembali.");
        }

        // Validasi item checkbox
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

        // Query Keranjang Belanja
        $cart = CartModel::where('user_id', $userId)->first();
        if (!$cart) {
            throw new Exception("Keranjang belanja tidak ditemukan.");
        }

        // Ambil items yang dicentang
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

        // --- INTEGRASI LOGIC VOUCHER KETAT ---
        $voucherId = null;
        $discountAmount = 0.00;

        $cleanVoucherCode = isset($voucherCode) ? trim($voucherCode) : '';

        if ($cleanVoucherCode !== '') {
            $voucher = DB::table('vouchers')
                ->where('code', strtoupper($cleanVoucherCode))
                ->lockForUpdate()
                ->first();

            if (!$voucher) {
                throw new Exception("Voucher tidak dikenali.");
            }

            // PERBAIKAN: Menggunakan method Carbon isBefore() dan isAfter() agar tidak memicu MethodDoesNotExistsException
            $now = now();
            if (!$voucher->is_active || $now->isBefore($voucher->starts_at) || $now->isAfter($voucher->ends_at)) {
                throw new Exception("Voucher sudah kedaluwarsa atau sudah tidak aktif.");
            }

            if ($voucher->usage_limit > 0 && $voucher->used_count >= $voucher->usage_limit) {
                throw new Exception("Maaf, kuota penggunaan voucher ini sudah habis.");
            }

            if ($totalAmount < $voucher->min_spend) {
                throw new Exception("Minimal belanja untuk menggunakan voucher ini adalah Rp" . number_format($voucher->min_spend, 0, ',', '.'));
            }

            if ($voucher->discount_type === 'percentage') {
                $discountAmount = ($totalAmount * $voucher->discount_value) / 100;

                if (!is_null($voucher->max_discount) && $discountAmount > $voucher->max_discount) {
                    $discountAmount = $voucher->max_discount;
                }
            } else {
                $discountAmount = $voucher->discount_value;
            }

            if ($discountAmount > $totalAmount) {
                $discountAmount = $totalAmount;
            }

            $voucherId = $voucher->id;
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        // Buat entitas domain lengkap
        $order = new Order(
            id: null,
            orderNumber: $orderNumber,
            userId: $userId,
            totalAmount: $totalAmount,
            status: 'pending',
            shippingAddress: $shippingAddress,
            items: $domainItems,
            voucherId: $voucherId,
            discountAmount: $discountAmount
        );

        return DB::transaction(function () use ($order, $cart, $cartItemIds, $voucherId) {
            // Simpan transaksi order ke database lewat repository
            $createdOrder = $this->orderRepository->create($order);

            // JIKA pakai voucher, potong kuota / naikkan used_count
            if ($voucherId) {
                DB::table('vouchers')
                    ->where('id', $voucherId)
                    ->increment('used_count');
            }

            // Hapus item dari keranjang
            $cart->items()->whereIn('id', $cartItemIds)->delete();

            return $createdOrder;
        });
    }
}
