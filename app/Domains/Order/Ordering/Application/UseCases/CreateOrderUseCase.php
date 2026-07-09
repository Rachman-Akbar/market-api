<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Domain\Entities\Order;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use App\Domains\Order\Ordering\Domain\Services\ShippingCostCalculator; // <--- IMPORT SERVICE BARU
use App\Domains\Order\Cart\Infrastructure\Persistence\Models\CartModel;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private AddressRepositoryInterface $addressRepository,
        private ShippingCostCalculator $shippingCalculator // <--- INJECT SERVICE DI SINI
    ) {}

    public function execute(
        string $userId,
        int $addressId,
        array $cartItemIds = [],
        string $courier = 'jne',
        string $paymentMethod = 'qris',
        ?string $voucherCode = null
    ): Order {

        if (empty(trim($userId))) {
            throw new Exception("Sesi Anda telah berakhir. Silakan login kembali.");
        }
        if (empty($cartItemIds)) {
            throw new Exception("Pilih minimal satu produk untuk melakukan checkout.");
        }

        // Ambil data alamat via Repository
        $addressRow = $this->addressRepository->findByIdAndOwner($addressId, $userId, null);
        if (!$addressRow) {
            throw new Exception("Alamat pengiriman tidak ditemukan.");
        }

        $shippingAddress = $addressRow->full_address . ", " . $addressRow->city . " " . $addressRow->postal_code;
        $destinationId = $addressRow->komerce_destination_id;

        if (empty($destinationId)) {
            throw new Exception("Wilayah alamat belum terhubung dengan sistem logistik Komerce.");
        }

        // --- PROSES HITUNG ONGKIR DINAMIS ---
        // Panggil service kalkulator dengan menyuplai koordinat milik customer
        $shippingCost = $this->shippingCalculator->calculate(
            customerLat: (float) $addressRow->latitude,
            customerLng: (float) $addressRow->longitude
        );
        // -------------------------------------

        // Query Keranjang Belanja
        $cart = CartModel::where('user_id', $userId)->first();
        if (!$cart) {
            throw new Exception("Keranjang belanja tidak ditemukan.");
        }

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

        // Integrasi Voucher (Ketat DB)
        $voucherId = null;
        $discountAmount = 0.00;
        $cleanVoucherCode = isset($voucherCode) ? trim($voucherCode) : '';

        if ($cleanVoucherCode !== '') {
            $voucher = DB::table('vouchers')->where('code', strtoupper($cleanVoucherCode))->lockForUpdate()->first();
            if (!$voucher) {
                throw new Exception("Voucher tidak dikenali.");
            }

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
        $mockMidtransSnapToken = "snap-token-" . bin2hex(random_bytes(8));

        // Buat Entitas Domain Utama
        $order = new Order(
            id: null,
            orderNumber: $orderNumber,
            userId: $userId,
            totalAmount: $totalAmount,
            shippingCost: $shippingCost, // <--- Sudah Otomatis Dinamis (0 atau 15000)
            discountAmount: $discountAmount,
            status: 'pending',
            paymentStatus: 'unpaid',
            paymentMethod: $paymentMethod,
            snapToken: $mockMidtransSnapToken,
            shippingAddress: $shippingAddress,
            destinationId: $destinationId,
            courier: $courier,
            items: $domainItems,
            voucherId: $voucherId
        );

        return DB::transaction(function () use ($order, $cart, $cartItemIds, $voucherId) {
            $createdOrder = $this->orderRepository->create($order);

            if ($voucherId) {
                DB::table('vouchers')->where('id', $voucherId)->increment('used_count');
            }

            $cart->items()->whereIn('id', $cartItemIds)->delete();

            return $createdOrder;
        });
    }
}
