<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Ordering\Domain\Entities\Order as DomainOrder;
use App\Domains\Order\Ordering\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderItemModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(DomainOrder $order): DomainOrder
    {
        return DB::transaction(function () use ($order) {
            $orderModel = OrderModel::create([
                'order_number' => $order->orderNumber,
                'user_id' => $order->userId,
                'total_amount' => $order->totalAmount,
                'status' => $order->status,
                'shipping_address' => $order->shippingAddress,
            ]);

            $savedDomainItems = [];

            foreach ($order->items as $item) {
                // Simpan ke database dan ambil instance modelnya yang sudah ber-ID
                $itemModel = OrderItemModel::create([
                    'order_id'     => $orderModel->id,
                    'product_id'   => $item->productId,
                    'store_id'     => $item->storeId,
                    'product_name' => $item->productName,
                    'sku'          => $item->sku,
                    'price'        => $item->price,
                    'quantity'     => $item->quantity,
                ]);

                // PERBAIKAN: Buat ulang entitas DomainOrderItem dengan menyertakan ID asli dari DB
                $savedDomainItems[] = new DomainOrderItem(
                    id: $itemModel->id, // <--- ID sekarang sudah terisi!
                    productId: $item->productId,
                    storeId: $item->storeId,
                    productName: $item->productName,
                    sku: $item->sku,
                    price: $item->price,
                    quantity: $item->quantity
                );
            }

            // Set data ID utama dan items terbaru ke objek order domain
            $order->id = $orderModel->id;
            $order->items = $savedDomainItems;

            return $order;
        });
    }

    public function findById(int $id): ?DomainOrder
    {
        $model = OrderModel::with('items')->find($id);
        if (!$model) return null;

        return $this->mapToDomain($model);
    }

    public function getByUserId(string $userId): array
    {
        $models = OrderModel::with('items')->where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->mapToDomain($model))->toArray();
    }

    private function mapToDomain(OrderModel $model): DomainOrder
    {
        $items = $model->items->map(function ($item) {
            return new DomainOrderItem(
                $item->id,
                $item->product_id,
                $item->store_id,
                $item->product_name,
                $item->sku,
                (float) $item->price,
                $item->quantity
            );
        })->toArray();

        return new DomainOrder(
            $model->id,
            $model->order_number,
            $model->user_id,
            (float) $model->total_amount,
            $model->status,
            $model->shipping_address,
            $items
        );
    }
}
