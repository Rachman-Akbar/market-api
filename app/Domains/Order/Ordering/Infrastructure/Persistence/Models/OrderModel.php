<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $guarded = ['id'];

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrderModel::class, 'order_id');
    }
}
