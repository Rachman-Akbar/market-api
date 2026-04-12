<?php

namespace App\Providers;

use App\Domains\Realtime\Application\Listeners\SyncCourierAssignedToRealtimeListener;
use App\Domains\Realtime\Application\Listeners\SyncOrderPlacedToRealtimeListener;
use App\Domains\Realtime\Application\Listeners\SyncOrderUpdatedToRealtimeListener;
use App\Events\CourierAssigned;
use App\Events\OrderPlaced;
use App\Events\OrderUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class MarketplaceEventServiceProvider extends EventServiceProvider
{
    /**
     * Realtime mirror subscriptions. Business state stays in MySQL;
     * listeners project event snapshots into Firebase read models.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            SyncOrderPlacedToRealtimeListener::class,
        ],
        OrderUpdated::class => [
            SyncOrderUpdatedToRealtimeListener::class,
        ],
        CourierAssigned::class => [
            SyncCourierAssignedToRealtimeListener::class,
        ],
    ];
}
