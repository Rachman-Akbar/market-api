<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Domain\Entities;

final class Order
{
    /**
     * @param SubOrder[] $subOrders
     */
    public function __construct(
        public ?int $id,
        public string $orderNumber,
        public string $userId,
        public ?int $voucherId,
        public float $totalAmount,
        public float $discountAmount,
        public string $status,
        public string $paymentStatus,
        public ?string $paymentMethod,
        public ?string $snapToken,
        public string $shippingAddress,
        public array $subOrders = [] // Menampung koleksi pecahan toko
    ) {}

    public function getFinalPay(): float
    {
        return max(0.00, ($this->totalAmount - $this->discountAmount));
    }

    /**
     * Helper untuk mendapatkan ongkir spesifik milik suatu toko
     */
    public function getShippingCostByStore(int $storeId): float
    {
        foreach ($this->subOrders as $subOrder) {
            if ($subOrder->storeId === $storeId) {
                return $subOrder->shippingCost;
            }
        }
        return 0.00;
    }

    /**
     * Helper untuk mendapatkan kurir spesifik milik suatu toko
     */
    public function getCourierByStore(int $storeId): ?string
    {
        foreach ($this->subOrders as $subOrder) {
            if ($subOrder->storeId === $storeId) {
                return $subOrder->courier;
            }
        }
        return null;
    }
}
