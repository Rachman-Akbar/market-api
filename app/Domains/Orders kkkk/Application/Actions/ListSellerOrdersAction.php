<?php

namespace App\Domains\Orders\Application\Actions;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

final class ListSellerOrdersAction
{
    /**
     * @return Collection<int, Order>
     */
    public function execute(string $sellerId): Collection
    {
        return Order::query()
            ->with(['items.product', 'payment'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->get();
    }
}
