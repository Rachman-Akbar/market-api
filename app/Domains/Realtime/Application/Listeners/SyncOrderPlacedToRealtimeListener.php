<?php

namespace App\Domains\Realtime\Application\Listeners;

use App\Domains\Realtime\Application\Actions\SyncOrderToRealtimeAction;
use App\Events\OrderPlaced;

final class SyncOrderPlacedToRealtimeListener
{
    public function __construct(private readonly SyncOrderToRealtimeAction $sync) {}

    public function handle(OrderPlaced $event): void
    {
        $this->sync->execute($event->orderId);
    }
}
