<?php

namespace App\Domains\Orders\Application\Actions;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

final class ListBuyerOrdersAction
{
    /**
     * @return Collection<int, Order>
     */
    public function execute(string $buyerId): Collection
    {
        return Order::query()
            ->with(['items.product', 'payment'])
            ->where('buyer_id', $buyerId)
            ->latest()
            ->get();
    }
}
