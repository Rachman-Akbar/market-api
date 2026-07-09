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
                    'destination_id'     => $subOrder->destinationId,
                    'status'             => 'pending'
                ]);

                // 3. Simpan item produk di bawah naungan sub-order toko ini
                foreach ($subOrder->items as $item) {
                    OrderItemModel::create([
                        'sub_order_id' => $subOrderModel->id,
                        'product_id'   => $item->productId,
                        'product_name' => $item->productName,
                        'sku'          => $item->sku,
                        'price'        => $item->price,
                        'quantity'     => $item->quantity,
                    ]);
                }
            }

            // 4. Reload relasi bertingkat secara utuh
            $savedModel = OrderModel::with('subOrders.items')->find($orderModel->id);
            return $this->mapper->toDomain($savedModel);
        });
    }

    public function update(DomainOrder $order): void
    {
        OrderModel::where('id', $order->id)->update([
            'status'              => $order->status,
            'payment_status'      => $order->paymentStatus,
            'midtrans_snap_token' => $order->snapToken,
        ]);

        // Clear cache jika status pembayaran atau status order berubah
        $model = OrderModel::find($order->id);
        if ($model) {
            $this->clearUserOrderCache($model->user_id);
        }
    }

    public function findById(int $id): ?DomainOrder
    {
        $model = OrderModel::with('subOrders.items')->find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByOrderNumber(string $orderNumber): ?DomainOrder
    {
        $model = OrderModel::with('subOrders.items')->where('order_number', $orderNumber)->first();
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function getByUserId(string $userId): array
    {
        $models = OrderModel::with('subOrders.items')->where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->mapper->toDomain($model))->toArray();
    }

    /**
     * Mengambil list order ber-pagination lengkap dengan mekanisme Caching
     */
    public function paginateForUser(?string $userId, array $filters, int $perPage): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        
        // Membuat unique cache key berdasarkan parameter user, halaman, dan limit data
        $cacheKey = "user_orders_cache_" . ($userId ?? 'all') . "_p{$page}_limit{$perPage}";

        // Cache disimpan selama 10 menit (600 detik)
        return Cache::remember($cacheKey, 600, function () use ($userId, $perPage) {
            $query = OrderModel::with('subOrders.items');

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $query->orderBy('created_at', 'desc');

            $eloquentPaginator = $query->paginate($perPage);

            // Mapping koleksi Eloquent Model di dalam Paginator ke Domain Entity
            return $eloquentPaginator->through(function ($model) {
                return $this->mapper->toDomain($model);
            });
        });
    }

    /**
     * Helper privat untuk membersihkan cache pagination milik user tertentu
     */
    private function clearUserOrderCache(string $userId): void
    {
        // Menghapus cache halaman 1 sampai 5 (antisipasi halaman awal yang sering dibuka)
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("user_orders_cache_{$userId}_p{$i}_limit15");
            Cache::forget("user_orders_cache_all_p{$i}_limit15");
        }
    }
}