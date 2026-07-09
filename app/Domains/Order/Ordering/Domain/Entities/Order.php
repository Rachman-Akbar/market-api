<?php

namespace App\Domains\Order\Ordering\Domain\Entities;

class Order
{
    public function __construct(
        public ?int $id,
        public string $orderNumber,
        public string $userId,
        public float $totalAmount,       // Total murni harga produk
        public float $shippingCost,      // <--- TAMBAHKAN INI (Ongkir Komerce)
        public float $discountAmount,    // Potongan voucher
        public string $status,           // Status Logistik
        public string $paymentStatus,    // <--- TAMBAHKAN INI (Status Midtrans)
        public ?string $paymentMethod,   // <--- TAMBAHKAN INI (e.g. qris, bank_transfer)
        public ?string $snapToken,       // <--- TAMBAHKAN INI (Token transaksi Midtrans)
        public string $shippingAddress,
        public string $destinationId,    // <--- TAMBAHKAN INI (Komerce Destination ID)
        public ?string $courier,         // <--- TAMBAHKAN INI
        public array $items = [],
        public ?int $voucherId = null
    ) {}

    /**
     * Menghitung total bersih yang wajib dibayar oleh pembeli
     */
    public function getFinalPay(): float
    {
        return ($this->totalAmount + $this->shippingCost) - $this->discountAmount;
    }
}
