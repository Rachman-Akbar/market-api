<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubOrderModel extends Model
{
    protected $table = 'sub_orders';
    protected $guarded = ['id'];

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'sub_order_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel::class, 'store_id');
    }
}
