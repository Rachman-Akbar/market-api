<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use Exception;

class CancelOrderUseCase
{
    public function execute(int $orderId): bool
    {
        $order = OrderModel::findOrFail($orderId);
        if (in_array($order->status, ['completed', 'cancelled'])) {
            throw new Exception("Order yang sudah selesai atau dibatalkan tidak bisa di-cancel lagi.");
        }
        return $order->update(['status' => 'cancelled']);
    }
}
