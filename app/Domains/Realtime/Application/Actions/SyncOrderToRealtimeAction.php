<?php

namespace App\Domains\Realtime\Application\Actions;

use App\Models\Order;

final class SyncOrderToRealtimeAction
{
    /**
     * This method is the integration point where order snapshots are mirrored
     * to Firebase/Firestore. Keep business rules in domain actions, and only
     * publish read-model snapshots from this boundary.
     */
    public function execute(int $orderId): void
    {
        $order = Order::query()->with(['items', 'payment'])->findOrFail($orderId);

        // Placeholder integration for Firestore mirror:
        // realtime/orders/{orderId} => order snapshot
        logger()->info('Realtime mirror sync placeholder', [
            'target' => 'realtime/orders/' . $order->id,
            'order_status' => $order->status,
        ]);
    }
}
