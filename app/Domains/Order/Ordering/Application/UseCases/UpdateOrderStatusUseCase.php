<?php

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;

class UpdateOrderStatusUseCase
{
    public function execute(int $orderId, string $status): bool
    {
        $order = OrderModel::findOrFail($orderId);
        return $order->update(['status' => $status]);
    }
}
