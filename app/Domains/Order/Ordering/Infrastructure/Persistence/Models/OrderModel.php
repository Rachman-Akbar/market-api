<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'voucher_id',
        'total_amount',
        'discount_amount',
        'shipping_cost', // <--- TAMBAHKAN INI
        'status',
        'payment_status', // <--- TAMBAHKAN INI
        'payment_method', // <--- TAMBAHKAN INI
        'midtrans_snap_token', // <--- TAMBAHKAN INI
        'shipping_address',
        'destination_id', // <--- TAMBAHKAN INI
        'courier' // <--- TAMBAHKAN INI
    ];

    public function items()
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
