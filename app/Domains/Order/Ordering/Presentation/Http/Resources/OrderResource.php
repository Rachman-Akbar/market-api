<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Presentation\Http\Resources;

use App\Domains\Order\Ordering\Presentation\Http\Resources\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $order = $this->resource;

        // 1. Hitung total ongkir kumulatif secara dinamis dari sub-orders
        $shippingCost = 0;
        $subOrdersArray = $this->read($order, 'subOrders') ?? [];
        
        foreach ($subOrdersArray as $subOrder) {
            $shippingCost += (float) ($this->read($subOrder, 'shippingCost') ?? $this->read($subOrder, 'shipping_cost') ?? 0);
        }

        // 2. Satukan semua items dari berbagai sub-order toko ke dalam satu array linier (jika frontend butuh data flat)
        $allItems = [];
        foreach ($subOrdersArray as $subOrder) {
            $itemsInSubOrder = $this->read($subOrder, 'items') ?? [];
            foreach ($itemsInSubOrder as $item) {
                $allItems[] = $item;
            }
        }

        $totalAmount = (float) ($this->read($order, 'totalAmount') ?? $this->read($order, 'total_amount') ?? 0);
        $discountAmount = (float) ($this->read($order, 'discountAmount') ?? $this->read($order, 'discount_amount') ?? 0);
        
        // Rumus Grand Total: (Total Belanja + Total Ongkir) - Diskon Voucher
        $grandTotal = ($totalAmount + $shippingCost) - $discountAmount;

        return [
            'id'             => $this->read($order, 'id'),
            'order_number'   => $this->readValue($this->read($order, 'orderNumber') ?? $this->read($order, 'order_number')),
            'user_id'        => $this->read($order, 'userId') ?? $this->read($order, 'user_id'),
            'status'         => $this->readValue($this->read($order, 'status')),
            'payment_status' => $this->readValue($this->read($order, 'paymentStatus') ?? $this->read($order, 'payment_status')),

            // Keuangan & Biaya ter-kalkulasi
            'total_amount'    => $this->readMoney($totalAmount),
            'shipping_cost'   => $this->readMoney($shippingCost),
            'discount_amount' => $this->readMoney($discountAmount),
            'grand_total'     => $this->readMoney($this->read($order, 'grandTotal') ?? $this->read($order, 'grand_total') ?? $grandTotal),

            // Logistik & Pembayaran
            'shipping_address' => $this->readAddress($this->read($order, 'shippingAddress') ?? $this->read($order, 'shipping_address')),
            'destination_id'   => $this->read($order, 'destinationId') ?? $this->read($order, 'destination_id'),
            'courier'          => $this->read($order, 'courier'),
            'payment_method'   => $this->read($order, 'paymentMethod') ?? $this->read($order, 'payment_method'),
            'snap_token'       => $this->read($order, 'snapToken') ?? $this->read($order, 'midtrans_snap_token'),

            // Memasukkan list item gabungan
            'items'      => OrderItemResource::collection($this->toCollection($allItems)),
            'created_at' => $this->readDate($this->read($order, 'createdAt') ?? $this->read($order, 'created_at')),
            'updated_at' => $this->readDate($this->read($order, 'updatedAt') ?? $this->read($order, 'updated_at')),
        ];
    }

    private function read(object|array|null $source, string $key): mixed
    {
        if ($source === null) return null;
        if (is_array($source)) return $source[$key] ?? null;
        if (method_exists($source, $key)) return $source->{$key}();
        return $source->{$key} ?? null;
    }

    private function readValue(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'value')) return $value->value();
        return $value;
    }

    private function readMoney(mixed $value): float
    {
        if (is_object($value) && method_exists($value, 'amount')) return (float) $value->amount();
        return (float) $value;
    }

    private function readAddress(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'toArray')) return $value->toArray();
        return $value;
    }

    private function readDate(mixed $value): ?string
    {
        if ($value === null) return null;
        if (is_object($value) && method_exists($value, 'toISOString')) return $value->toISOString();
        if (is_object($value) && method_exists($value, 'format')) return $value->format(DATE_ATOM);
        return (string) $value;
    }

    private function toCollection(mixed $value): array
    {
        if ($value === null) return [];
        if ($value instanceof \Illuminate\Support\Collection) return $value->all();
        if (is_array($value)) return $value;
        if ($value instanceof \Traversable) return iterator_to_array($value);
        return [];
    }
}