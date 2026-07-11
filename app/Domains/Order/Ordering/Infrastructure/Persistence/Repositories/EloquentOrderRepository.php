<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Ordering\Domain\Entities\Order as DomainOrder;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderItemModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Mappers\OrderMapper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(private OrderMapper $mapper) {}

    public function create(DomainOrder $order): DomainOrder
    {
        return DB::transaction(function () use ($order) {
            // Hapus cache daftar order milik user ini karena ada data transaksi baru masuk
            // Kita hapus base key pola pagination untuk user ini
            $this->clearUserOrderCache($order->userId);

            // 1. Simpan Data Global (Parent Order)
            $orderModel = OrderModel::create($this->mapper->toPersistenceArray($order));

            // 2. Simpan pecahan transaksi per toko (Sub Order)
            foreach ($order->subOrders as $subOrder) {
                $subOrderModel = SubOrderModel::create([
                    'order_id'           => $orderModel->id,
                    'store_id'           => $subOrder->storeId,
                    'sub_order_number'   => $subOrder->subOrderNumber,
                    'total_items_price'  => $subOrder->totalItemsPrice,
                    'shipping_cost'      => $subOrder->shippingCost,
                    'courier'            => $subOrder->courier,
                    'service'            => $subOrder->service,
                    'destination_id'     => $subOrder->destinationId,
                    'status'             => 'pending'
                ]);

                // 3. Simpan item produk di bawah naungan sub-order toko ini
                foreach ($subOrder->items as $item) {
                    OrderItemModel::create([
                        'sub_order_id' => $subOrderModel->id,
                        'product_id'   => $item->productId,
                        'variant_id'   => $item->variantId,
                        'product_name' => $item->productName,
                        'sku'          => $item->sku,
                        'price'        => $item->price,
                        'quantity'     => $item->quantity,
                    ]);
                }
            }

            // 4. Reload relasi bertingkat secara utuh
            $savedModel = OrderModel::with(['subOrders.items', 'subOrders.store'])->find($orderModel->id);
            return $this->mapper->toDomain($savedModel);
        });
    }

    public function update(DomainOrder $order): void
    {
        DB::transaction(function () use ($order): void {
            $model = OrderModel::query()->lockForUpdate()->find($order->id);
            if (!$model) {
                return;
            }

            $wasCancelled = (string) $model->status === 'cancelled';
            $becomesCancelled = $order->status === 'cancelled';

            if (!$wasCancelled && $becomesCancelled) {
                $items = DB::table('order_items')
                    ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                    ->where('sub_orders.order_id', $model->id)
                    ->whereNotNull('order_items.variant_id')
                    ->select('order_items.variant_id', 'order_items.quantity')
                    ->get();

                foreach ($items as $item) {
                    DB::table('product_variants')
                        ->where('id', (int) $item->variant_id)
                        ->increment('stock', (int) $item->quantity);
                }
            }

            $model->update([
                'status' => $order->status,
                'payment_status' => $order->paymentStatus,
                'payment_method' => $order->paymentMethod,
                'midtrans_snap_token' => $order->snapToken,
            ]);

            if ($order->status === 'processing') {
                SubOrderModel::where('order_id', $model->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'processing', 'updated_at' => now()]);
            } elseif ($order->status === 'cancelled') {
                SubOrderModel::where('order_id', $model->id)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->update(['status' => 'cancelled', 'updated_at' => now()]);
            }

            $this->clearUserOrderCache((string) $model->user_id);
        });
    }

    public function findById(int $id): ?DomainOrder
    {
        $model = OrderModel::with(['subOrders.items', 'subOrders.store'])->find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByOrderNumber(string $orderNumber): ?DomainOrder
    {
        $model = OrderModel::with(['subOrders.items', 'subOrders.store'])->where('order_number', $orderNumber)->first();
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function getByUserId(string $userId): array
    {
        $models = OrderModel::with(['subOrders.items', 'subOrders.store'])->where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->mapper->toDomain($model))->toArray();
    }

    /**
     * Mengambil list order ber-pagination lengkap dengan mekanisme Caching
     */
    public function paginateForUser(?string $userId, array $filters, int $perPage): LengthAwarePaginator
    {
        $page = max(1, (int) request()->query('page', 1));
        $version = (int) Cache::get('orders_cache_version', 1);
        $cacheKey = 'orders:' . $version . ':' . md5(json_encode([
            'user_id' => $userId,
            'filters' => $filters,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $filters, $perPage) {
            $query = OrderModel::with(['subOrders.items', 'subOrders.store']);

            if ($userId) {
                $query->where('user_id', $userId);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }
            if (!empty($filters['search'])) {
                $search = trim((string) $filters['search']);
                $query->where('order_number', 'like', "%{$search}%");
            }

            return $query->latest()->paginate($perPage)->through(
                fn($model) => $this->mapper->toDomain($model)
            );
        });
    }

    /**
     * Helper privat untuk membersihkan cache pagination milik user tertentu
     */
    private function clearUserOrderCache(string $userId): void
    {
        $version = (int) Cache::get('orders_cache_version', 1);
        Cache::forever('orders_cache_version', $version + 1);
    }
}