<?php

namespace App\Domains\Realtime\Application\Listeners;

use App\Domains\Realtime\Application\Actions\SyncOrderToRealtimeAction;
use App\Events\OrderUpdated;

final class SyncOrderUpdatedToRealtimeListener
{
    public function __construct(private readonly SyncOrderToRealtimeAction $sync) {}

    public function handle(OrderUpdated $event): void
    {
        $this->sync->execute($event->orderId);
    }
}
