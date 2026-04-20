<?php

namespace App\Domains\Realtime\Application\Listeners;

use App\Domains\Realtime\Application\Actions\SyncOrderToRealtimeAction;
use App\Events\CourierAssigned;

final class SyncCourierAssignedToRealtimeListener
{
    public function __construct(private readonly SyncOrderToRealtimeAction $sync) {}

    public function handle(CourierAssigned $event): void
    {
        $this->sync->execute($event->orderId);
    }
}
