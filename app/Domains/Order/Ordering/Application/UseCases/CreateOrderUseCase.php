<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Ordering\Domain\Entities\Order;
use App\Domains\Order\Ordering\Domain\Entities\SubOrder;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use App\Domains\Order\Ordering\Domain\Services\ShippingCostCalculator;
use App\Domains\Order\Cart\Infrastructure\Persistence\Models\CartModel;
use App\Domains\Order\Payment\Infrastructure\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private AddressRepositoryInterface $addressRepository,
        private ShippingCostCalculator $shippingCalculator,
        private MidtransService $midtransService,
        private ProductForCartReaderInterface $productReader
    ) {}

    public function execute(
        string $userId,
        ?int $addressId,
        array $cartItemIds = [],
        string $courier = 'jne',
        string $paymentMethod = 'midtrans',
        ?string $voucherCode = null
    ): Order {

        if (empty(trim($userId))) {
            throw new Exception("Sesi Anda telah berakhir. Silakan login kembali.");
        }
        if (empty($cartItemIds)) {
            throw new Exception("Pilih minimal satu produk untuk melakukan checkout.");
        }

        // 1. Inisialisasi Data Pengiriman Global
        $shippingAddress = "Ambil Sendiri di Toko Utama";
        $destinationId = "STORE-PICKUP";
        $shippingContext = [];

        if ($courier !== 'ambil_sendiri') {
            if (!$addressId) {
                throw new Exception("Alamat pengiriman wajib ditentukan.");
            }

            $addressRow = $this->addressRepository->findByIdAndOwner($addressId, $userId, null);
            if (!$addressRow) {
                throw new Exception("Alamat pengiriman tidak ditemukan.");
            }

            $shippingAddress = $addressRow->full_address . ", " . $addressRow->city_or_regency . " " . $addressRow->postal_code;
            $destinationId = $addressRow->komerce_destination_id;

            $shippingContext = [
                'latitude'       => (float) $addressRow->latitude,
                'longitude'      => (float) $addressRow->longitude,
                'destination_id' => $destinationId,
                'weight'         => 1000
            ];
        }

        // 2. Hitung Ongkir Base Global (Nanti bisa dipecah per toko jika strategi ongkirnya sudah multi-origin)
        $globalShippingCost = $this->shippingCalculator->calculate($courier, $shippingContext);

        // 3. Ambil Item Dari Keranjang Belanja
        $cart = CartModel::where('user_id', $userId)->first();
        if (!$cart) {
            throw new Exception("Keranjang belanja tidak ditemukan.");
        }

        $selectedItems = $cart->items()->whereIn('id', $cartItemIds)->get();
        if ($selectedItems->isEmpty()) {
            throw new Exception("Item yang dipilih tidak ditemukan di keranjang belanja Anda.");
        }

        // Kelompokkan data mentah berdasarkan Store ID untuk kebutuhan Split Order
        $groupedCartItems = [];
$totalAmount = 0;

foreach ($selectedItems as $cartItem) {
    $variantDetails = $this->productReader->getVariantDetails((int) $cartItem->product_variant_id);

    if (!$variantDetails) {
        throw new Exception("Data varian tidak ditemukan.");
    }

    $realPrice   = (float) $variantDetails->getPrice()->getAmount(); 
    $realName    = $variantDetails->getName();
    $realSku     = $variantDetails->getSku();
    $realStoreId = $variantDetails->getStoreId(); 

    $totalAmount += ($realPrice * $cartItem->quantity);

    $groupedCartItems[$realStoreId][] = [
        'productId'   => $variantDetails->getProductId(), // Dilempar ke kolom product_id (Lolos Foreign Key)
        'variantId'   => $variantDetails->getId(),        // Tetap simpan ID Varian aslinya jika diperlukan
        'productName' => $realName,                       // Nama Varian (misal: "Hitam - L")
        'sku'         => $realSku,                        // SKU Varian
        'price'       => $realPrice,
        'quantity'    => $cartItem->quantity
    ];
}

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        // 4. Operasi Database Terbuka Bergaransi Transaksi
        return DB::transaction(function () use (
            $userId, $orderNumber, $totalAmount, $globalShippingCost, $shippingAddress,
            $destinationId, $courier, $paymentMethod, $voucherCode, $groupedCartItems, $cart, $cartItemIds
        ) {

            // Logika Validasi Voucher Global
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

            // 5. Pemetaan Koleksi Sub-Order Toko beserta Item di dalamnya
            $domainSubOrders = [];
            $totalStores = count($groupedCartItems);

            foreach ($groupedCartItems as $storeId => $itemsArray) {
                $subOrderItems = [];
                $subOrderTotalItemsPrice = 0;

                foreach ($itemsArray as $itemData) {
                    $subOrderTotalItemsPrice += ($itemData['price'] * $itemData['quantity']);

                    $subOrderItems[] = new OrderItem(
                        id: null,
                        productId: $itemData['productId'],
                        storeId: $storeId,
                        productName: $itemData['productName'],
                        sku: $itemData['sku'],
                        price: $itemData['price'],
                        quantity: $itemData['quantity']
                    );
                }

                // Adil membagi ongkir rata ke tiap toko sebagai nilai default (atau disesuaikan kalkulasi per toko)
                $allocatedShippingCost = $globalShippingCost / max(1, $totalStores);

                $domainSubOrders[] = new SubOrder(
                    id: null,
                    storeId: $storeId,
                    subOrderNumber: $orderNumber . '-S' . $storeId,
                    totalItemsPrice: $subOrderTotalItemsPrice,
                    shippingCost: $allocatedShippingCost,
                    courier: $courier,
                    destinationId: $destinationId,
                    status: 'pending',
                    trackingNumber: null,
                    items: $subOrderItems
                );
            }

            // 6. Hitung Total Pembayaran Bersih
            $finalPay = ($totalAmount + $globalShippingCost) - $discountAmount;
            $midtransSnapToken = null;

            if ($paymentMethod === 'midtrans' && $finalPay > 0) {
                $midtransSnapToken = $this->midtransService->createSnapToken([
                    'order_id'     => $orderNumber,
                    'gross_amount' => (int) $finalPay,
                    'user_id'      => $userId
                ]);
            }

            // 7. Instansiasi Rich Domain Order Baru dengan named parameter yang benar
            $order = new Order(
                id: null,
                orderNumber: $orderNumber,
                userId: $userId,
                voucherId: $voucherId,
                totalAmount: $totalAmount + $globalShippingCost, // Sesuai DDL: Total belanja ditambah total ongkir
                discountAmount: $discountAmount,
                status: 'pending',
                paymentStatus: 'unpaid',
                paymentMethod: $paymentMethod,
                snapToken: $midtransSnapToken,
                shippingAddress: $shippingAddress,
                subOrders: $domainSubOrders // Masukkan array sub-orders ke sini!
            );

            // Simpan ke DB lewat repository
            $createdOrder = $this->orderRepository->create($order);

            if ($voucherId) {
                DB::table('vouchers')->where('id', $voucherId)->increment('used_count');
            }

            // Hapus item dari checkout cart
            $cart->items()->whereIn('id', $cartItemIds)->delete();

            return $createdOrder;
        });
    }
}
