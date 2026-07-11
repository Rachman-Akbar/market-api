<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Models\CartModel;
use App\Domains\Order\Ordering\Domain\Entities\Order;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem;
use App\Domains\Order\Ordering\Domain\Entities\SubOrder;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Payment\Domain\Entities\Payment;
use App\Domains\Order\Payment\Domain\Repositories\PaymentRepositoryInterface;
use App\Domains\Order\Payment\Infrastructure\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private AddressRepositoryInterface $addressRepository,
        private ProductForCartReaderInterface $productReader,
        private GetShippingOptionsUseCase $shippingOptionsUseCase,
        private MidtransService $midtransService,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(
        string $userId,
        ?int $addressId,
        array $cartItemIds,
        string $courier,
        ?string $service,
        string $paymentMethod,
        ?string $voucherCode = null
    ): Order {
        if (trim($userId) === '') {
            throw new RuntimeException('Sesi Anda telah berakhir. Silakan login kembali.');
        }

        $ids = collect($cartItemIds)->map(fn($id) => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            throw new RuntimeException('Pilih minimal satu produk untuk melakukan checkout.');
        }

        $cart = CartModel::where('user_id', $userId)->first();
        if (!$cart) {
            throw new RuntimeException('Keranjang belanja tidak ditemukan.');
        }

        $selectedItems = $cart->items()->whereIn('id', $ids)->get();
        if ($selectedItems->count() !== $ids->count()) {
            throw new RuntimeException('Sebagian item checkout tidak ditemukan di keranjang Anda.');
        }

        $groups = [];
        $itemsTotal = 0.0;
        foreach ($selectedItems as $cartItem) {
            $details = $this->productReader->getVariantDetails((int) $cartItem->product_variant_id);
            if (!$details) {
                throw new RuntimeException('Data varian produk tidak ditemukan.');
            }
            if ($details->getStock() < (int) $cartItem->quantity) {
                throw new RuntimeException("Stok {$details->getProductName()} tidak mencukupi.");
            }

            $lineTotal = $details->getPrice()->getAmount() * (int) $cartItem->quantity;
            $itemsTotal += $lineTotal;
            $groups[$details->getStoreId()][] = [
                'cart_item_id' => (int) $cartItem->id,
                'variant_id' => $details->getId(),
                'product_id' => $details->getProductId(),
                'store_id' => $details->getStoreId(),
                'store_name' => $details->getStoreName(),
                'product_name' => $details->getProductName(),
                'variant_name' => $details->getName(),
                'sku' => $details->getSku(),
                'price' => $details->getPrice()->getAmount(),
                'quantity' => (int) $cartItem->quantity,
            ];
        }

        $courier = strtolower(trim($courier));
        $service = $service ? strtoupper(trim($service)) : null;
        $paymentMethod = strtolower(trim($paymentMethod));

        if ($paymentMethod === 'tunai_toko' && $courier !== 'ambil_sendiri') {
            throw new RuntimeException('Bayar tunai di toko hanya tersedia untuk metode ambil sendiri.');
        }
        if ($paymentMethod === 'cod' && $courier === 'ambil_sendiri') {
            throw new RuntimeException('COD tidak tersedia untuk metode ambil sendiri.');
        }
        $shippingBreakdown = [];
        $shippingTotal = 0.0;
        $shippingAddress = 'Ambil sendiri di toko';
        $destinationId = 'STORE-PICKUP';

        if ($courier === 'ambil_sendiri') {
            foreach (array_keys($groups) as $storeId) {
                $shippingBreakdown[$storeId] = 0.0;
            }
            $service = 'PICKUP';
        } else {
            if (!$addressId) {
                throw new RuntimeException('Alamat pengiriman wajib ditentukan.');
            }

            $address = $this->addressRepository->findByIdAndOwner($addressId, $userId, null);
            if (!$address) {
                throw new RuntimeException('Alamat pengiriman tidak ditemukan.');
            }

            $shippingAddress = collect([
                $address->full_address,
                $address->subdistrict,
                $address->district,
                $address->city_or_regency,
                $address->province,
                $address->postal_code,
            ])->filter()->implode(', ');
            $destinationId = (string) $address->komerce_destination_id;

            $quote = $this->shippingOptionsUseCase->execute($userId, $addressId, $ids->all());
            $selectedOption = collect($quote['options'])->first(function (array $option) use ($courier, $service): bool {
                if (strtolower((string) $option['courier']) !== $courier) {
                    return false;
                }
                return !$service || strtoupper((string) $option['service']) === $service;
            });

            if (!$selectedOption) {
                throw new RuntimeException('Layanan pengiriman yang dipilih sudah tidak tersedia. Silakan hitung ulang ongkir.');
            }

            $service = strtoupper((string) $selectedOption['service']);
            $shippingTotal = (float) $selectedOption['cost'];
            foreach ($selectedOption['store_breakdown'] as $breakdown) {
                $shippingBreakdown[(int) $breakdown['store_id']] = (float) $breakdown['cost'];
            }
        }

        $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $customer = DB::table('users')->where('id', $userId)->first(['name', 'email']);

        return DB::transaction(function () use (
            $userId,
            $orderNumber,
            $itemsTotal,
            $shippingTotal,
            $shippingBreakdown,
            $shippingAddress,
            $destinationId,
            $courier,
            $service,
            $paymentMethod,
            $voucherCode,
            $groups,
            $cart,
            $ids,
            $customer
        ): Order {
            [$voucherId, $discountAmount, $shippingDiscountAmount] = $this->calculateVoucher(
                $voucherCode,
                $itemsTotal,
                $shippingTotal,
                array_keys($groups)
            );

            $subOrders = [];
            foreach ($groups as $storeId => $items) {
                $domainItems = [];
                $storeItemsTotal = 0.0;

                foreach ($items as $item) {
                    $affected = DB::table('product_variants')
                        ->where('id', $item['variant_id'])
                        ->where('stock', '>=', $item['quantity'])
                        ->decrement('stock', $item['quantity']);

                    if ($affected !== 1) {
                        throw new RuntimeException("Stok {$item['product_name']} berubah dan tidak lagi mencukupi.");
                    }

                    $storeItemsTotal += $item['price'] * $item['quantity'];
                    $label = $item['variant_name'] && $item['variant_name'] !== $item['product_name']
                        ? $item['product_name'] . ' - ' . $item['variant_name']
                        : $item['product_name'];

                    $domainItems[] = new OrderItem(
                        id: null,
                        productId: $item['product_id'],
                        variantId: $item['variant_id'],
                        storeId: (int) $storeId,
                        productName: $label,
                        sku: $item['sku'],
                        price: (float) $item['price'],
                        quantity: (int) $item['quantity']
                    );
                }

                $subOrders[] = new SubOrder(
                    id: null,
                    storeId: (int) $storeId,
                    storeName: (string) $items[0]['store_name'],
                    subOrderNumber: $orderNumber . '-S' . $storeId,
                    totalItemsPrice: $storeItemsTotal,
                    shippingCost: (float) ($shippingBreakdown[$storeId] ?? 0),
                    courier: $courier,
                    service: $service,
                    destinationId: $destinationId,
                    status: 'pending',
                    trackingNumber: null,
                    items: $domainItems
                );
            }

            $grossAmount = max(0.0, $itemsTotal + $shippingTotal - $discountAmount - $shippingDiscountAmount);
            $snapToken = null;
            if ($paymentMethod === 'midtrans' && $grossAmount > 0) {
                $snapToken = $this->midtransService->createSnapToken([
                    'order_id' => $orderNumber,
                    'gross_amount' => (int) round($grossAmount),
                    'user_id' => $userId,
                    'customer_name' => (string) ($customer->name ?? 'Customer'),
                    'customer_email' => (string) ($customer->email ?? ''),
                ]);
            }

            $order = new Order(
                id: null,
                orderNumber: $orderNumber,
                userId: $userId,
                voucherId: $voucherId,
                totalAmount: $itemsTotal + $shippingTotal,
                discountAmount: $discountAmount,
                shippingDiscountAmount: $shippingDiscountAmount,
                status: $grossAmount <= 0 ? 'processing' : 'pending',
                paymentStatus: $grossAmount <= 0 ? 'paid' : 'unpaid',
                paymentMethod: $paymentMethod,
                snapToken: $snapToken,
                shippingAddress: $shippingAddress,
                subOrders: $subOrders
            );

            $created = $this->orderRepository->create($order);
            $this->paymentRepository->save(new Payment(
                id: null,
                orderNumber: $orderNumber,
                transactionId: null,
                paymentMethod: $paymentMethod,
                amount: $grossAmount,
                status: $grossAmount <= 0 ? 'success' : 'pending',
                payload: null
            ));

            if ($voucherId) {
                DB::table('vouchers')->where('id', $voucherId)->increment('used_count');
            }

            $cart->items()->whereIn('id', $ids)->delete();
            return $created;
        });
    }

    private function calculateVoucher(?string $voucherCode, float $itemsTotal, float $shippingTotal, array $storeIds): array
    {
        $code = strtoupper(trim((string) $voucherCode));
        if ($code === '') {
            return [null, 0.0, 0.0];
        }

        $voucher = DB::table('vouchers')->where('code', $code)->lockForUpdate()->first();
        if (!$voucher) {
            throw new RuntimeException('Voucher tidak dikenali.');
        }

        if (!(bool) $voucher->is_active || now()->lt($voucher->starts_at) || now()->gt($voucher->ends_at)) {
            throw new RuntimeException('Voucher sudah kedaluwarsa atau tidak aktif.');
        }
        if ((int) $voucher->usage_limit > 0 && (int) $voucher->used_count >= (int) $voucher->usage_limit) {
            throw new RuntimeException('Kuota penggunaan voucher sudah habis.');
        }
        if ($voucher->store_id !== null && !in_array((int) $voucher->store_id, array_map('intval', $storeIds), true)) {
            throw new RuntimeException('Voucher tidak berlaku untuk toko dalam pesanan ini.');
        }
        if ($itemsTotal < (float) $voucher->min_spend) {
            throw new RuntimeException('Minimal belanja voucher belum terpenuhi.');
        }

        $type = strtolower((string) $voucher->discount_type);
        $value = (float) $voucher->discount_value;
        $maxDiscount = $voucher->max_discount !== null ? (float) $voucher->max_discount : null;
        $productDiscount = 0.0;
        $shippingDiscount = 0.0;

        if ($type === 'percentage') {
            $productDiscount = $itemsTotal * $value / 100;
        } elseif ($type === 'fixed') {
            $productDiscount = $value;
        } elseif ($type === 'free_shipping') {
            $shippingDiscount = $shippingTotal;
        } elseif ($type === 'shipping_percentage') {
            $shippingDiscount = $shippingTotal * $value / 100;
        } elseif ($type === 'shipping_fixed') {
            $shippingDiscount = $value;
        } else {
            throw new RuntimeException('Tipe voucher tidak didukung.');
        }

        if ($maxDiscount !== null) {
            $productDiscount = min($productDiscount, $maxDiscount);
            $shippingDiscount = min($shippingDiscount, $maxDiscount);
        }

        return [
            (int) $voucher->id,
            min($itemsTotal, max(0, $productDiscount)),
            min($shippingTotal, max(0, $shippingDiscount)),
        ];
    }
}
