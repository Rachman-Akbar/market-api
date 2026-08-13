<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stock\Application\Services;

use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductVariantModel;
use App\Domains\Seller\Stock\Domain\Repositories\StockMovementRepositoryInterface;
use App\Domains\Seller\Stock\Infrastructure\Persistence\Models\StockMovementModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class StockMovementService
{
    public function __construct(private StockMovementRepositoryInterface $repository) {}

    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $storeId);
    }

    public function adjust(array $data, ?int $sellerStoreId): StockMovementModel
    {
        return DB::transaction(function () use ($data, $sellerStoreId): StockMovementModel {
            $variant = ProductVariantModel::query()->lockForUpdate()->find((int) $data['variant_id']);

            if (! $variant) {
                throw new InvalidArgumentException('Varian produk tidak ditemukan.');
            }

            $storeId = $sellerStoreId ?? (int) $variant->store_id;

            if ((int) $variant->store_id !== $storeId) {
                throw new InvalidArgumentException('Stok varian bukan milik toko Anda.');
            }

            $delta = (int) $data['quantity_delta'];
            $nextBalance = (int) $variant->stock + $delta;

            if ($delta === 0) {
                throw new InvalidArgumentException('Perubahan stok tidak boleh nol.');
            }

            if ($nextBalance < 0) {
                throw new InvalidArgumentException('Stok tidak mencukupi untuk pengurangan tersebut.');
            }

            $variant->forceFill(['stock' => $nextBalance])->save();

            return $this->repository->save(new StockMovementModel([
                'store_id' => $storeId,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'type' => $delta > 0 ? 'inbound' : 'outbound',
                'quantity_delta' => $delta,
                'balance_after' => $nextBalance,
                'reference_type' => $data['reference_type'] ?? 'manual',
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
            ]));
        });
    }

    public function recordCheckoutReservation(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $items = DB::table('order_items')
                ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
                ->where('sub_orders.order_id', $orderId)
                ->select([
                    'order_items.id as order_item_id',
                    'order_items.product_id',
                    'order_items.variant_id',
                    'order_items.quantity',
                    'sub_orders.store_id',
                    'product_variants.stock as balance_after',
                ])
                ->get();

            foreach ($items as $item) {
                if ($this->repository->existsForOrderItem((int) $item->order_item_id, 'checkout-reserved')) {
                    continue;
                }

                $this->repository->save(new StockMovementModel([
                    'store_id' => (int) $item->store_id,
                    'product_id' => (int) $item->product_id,
                    'variant_id' => (int) $item->variant_id,
                    'order_id' => $orderId,
                    'order_item_id' => (int) $item->order_item_id,
                    'movement_key' => 'checkout-reserved',
                    'type' => 'outbound',
                    'quantity_delta' => -1 * (int) $item->quantity,
                    'balance_after' => (int) $item->balance_after,
                    'reference_type' => 'order',
                    'reference_id' => (string) $orderId,
                    'notes' => 'Reservasi stok saat checkout',
                    'occurred_at' => now(),
                ]));
            }
        });
    }

    public function syncOrderStatus(int $orderId, string $previousStatus, string $nextStatus): void
    {
        if ($previousStatus === $nextStatus) {
            return;
        }

        if ($nextStatus === 'cancelled') {
            $this->releaseCancelledOrder($orderId);
            return;
        }

        if (! in_array($nextStatus, ['processing', 'shipped', 'received', 'completed'], true)) {
            return;
        }

        DB::transaction(function () use ($orderId, $nextStatus): void {
            $items = DB::table('order_items')
                ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
                ->where('sub_orders.order_id', $orderId)
                ->select([
                    'order_items.id as order_item_id',
                    'order_items.product_id',
                    'order_items.variant_id',
                    'sub_orders.store_id',
                    'product_variants.stock as balance_after',
                ])
                ->get();

            foreach ($items as $item) {
                $key = 'order-status-' . $nextStatus;

                if ($this->repository->existsForOrderItem((int) $item->order_item_id, $key)) {
                    continue;
                }

                $this->repository->save(new StockMovementModel([
                    'store_id' => (int) $item->store_id,
                    'product_id' => (int) $item->product_id,
                    'variant_id' => (int) $item->variant_id,
                    'order_id' => $orderId,
                    'order_item_id' => (int) $item->order_item_id,
                    'movement_key' => $key,
                    'type' => 'status',
                    'quantity_delta' => 0,
                    'balance_after' => (int) $item->balance_after,
                    'reference_type' => 'order_status',
                    'reference_id' => $nextStatus,
                    'notes' => 'Status pesanan berubah menjadi ' . $nextStatus,
                    'occurred_at' => now(),
                ]));
            }
        });
    }


    public function syncSubOrderStatus(int $subOrderId, string $previousStatus, string $nextStatus): void
    {
        if ($previousStatus === $nextStatus) {
            return;
        }

        if ($nextStatus === 'cancelled') {
            $this->releaseCancelledSubOrder($subOrderId);
            return;
        }

        if (! in_array($nextStatus, ['processing', 'shipped', 'received', 'completed'], true)) {
            return;
        }

        DB::transaction(function () use ($subOrderId, $nextStatus): void {
            $items = DB::table('order_items')
                ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
                ->where('sub_orders.id', $subOrderId)
                ->select([
                    'order_items.id as order_item_id',
                    'order_items.product_id',
                    'order_items.variant_id',
                    'sub_orders.order_id',
                    'sub_orders.store_id',
                    'product_variants.stock as balance_after',
                ])
                ->get();

            foreach ($items as $item) {
                $key = 'sub-order-status-' . $nextStatus;

                if ($this->repository->existsForOrderItem((int) $item->order_item_id, $key)) {
                    continue;
                }

                $this->repository->save(new StockMovementModel([
                    'store_id' => (int) $item->store_id,
                    'product_id' => (int) $item->product_id,
                    'variant_id' => (int) $item->variant_id,
                    'order_id' => (int) $item->order_id,
                    'order_item_id' => (int) $item->order_item_id,
                    'movement_key' => $key,
                    'type' => 'status',
                    'quantity_delta' => 0,
                    'balance_after' => (int) $item->balance_after,
                    'reference_type' => 'sub_order_status',
                    'reference_id' => (string) $subOrderId,
                    'notes' => 'Status sub-order berubah menjadi ' . $nextStatus,
                    'occurred_at' => now(),
                ]));
            }
        });
    }

    private function releaseCancelledOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $items = DB::table('order_items')
                ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                ->where('sub_orders.order_id', $orderId)
                ->whereNotNull('order_items.variant_id')
                ->select([
                    'order_items.id as order_item_id',
                    'order_items.product_id',
                    'order_items.variant_id',
                    'order_items.quantity',
                    'sub_orders.store_id',
                ])
                ->get();

            foreach ($items as $item) {
                if ($this->repository->existsForOrderItem((int) $item->order_item_id, 'cancel-release')) {
                    continue;
                }

                $variant = ProductVariantModel::query()->lockForUpdate()->find((int) $item->variant_id);

                if (! $variant) {
                    continue;
                }

                $variant->increment('stock', (int) $item->quantity);
                $variant->refresh();

                $this->repository->save(new StockMovementModel([
                    'store_id' => (int) $item->store_id,
                    'product_id' => (int) $item->product_id,
                    'variant_id' => (int) $item->variant_id,
                    'order_id' => $orderId,
                    'order_item_id' => (int) $item->order_item_id,
                    'movement_key' => 'cancel-release',
                    'type' => 'release',
                    'quantity_delta' => (int) $item->quantity,
                    'balance_after' => (int) $variant->stock,
                    'reference_type' => 'order_cancelled',
                    'reference_id' => (string) $orderId,
                    'notes' => 'Stok dikembalikan karena pesanan dibatalkan',
                    'occurred_at' => now(),
                ]));
            }
        });
    }
    private function releaseCancelledSubOrder(int $subOrderId): void
    {
        DB::transaction(function () use ($subOrderId): void {
            $items = DB::table('order_items')
                ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                ->where('sub_orders.id', $subOrderId)
                ->whereNotNull('order_items.variant_id')
                ->select([
                    'order_items.id as order_item_id',
                    'order_items.product_id',
                    'order_items.variant_id',
                    'order_items.quantity',
                    'sub_orders.order_id',
                    'sub_orders.store_id',
                ])
                ->get();

            foreach ($items as $item) {
                if ($this->repository->existsForOrderItem((int) $item->order_item_id, 'cancel-release')) {
                    continue;
                }

                $variant = ProductVariantModel::query()->lockForUpdate()->find((int) $item->variant_id);

                if (! $variant) {
                    continue;
                }

                $variant->increment('stock', (int) $item->quantity);
                $variant->refresh();

                $this->repository->save(new StockMovementModel([
                    'store_id' => (int) $item->store_id,
                    'product_id' => (int) $item->product_id,
                    'variant_id' => (int) $item->variant_id,
                    'order_id' => (int) $item->order_id,
                    'order_item_id' => (int) $item->order_item_id,
                    'movement_key' => 'cancel-release',
                    'type' => 'release',
                    'quantity_delta' => (int) $item->quantity,
                    'balance_after' => (int) $variant->stock,
                    'reference_type' => 'sub_order_cancelled',
                    'reference_id' => (string) $subOrderId,
                    'notes' => 'Stok dikembalikan karena sub-order dibatalkan',
                    'occurred_at' => now(),
                ]));
            }
        });
    }

}
