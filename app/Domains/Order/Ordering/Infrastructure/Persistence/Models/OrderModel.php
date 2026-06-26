<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $fillable = ['order_number', 'user_id', 'total_amount', 'status', 'shipping_address'];

    public function items()
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
