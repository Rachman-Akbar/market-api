<?php

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    protected $table = 'orders';

    // Tambahkan voucher_id dan discount_amount di sini
    protected $fillable = [
        'order_number',
        'user_id',
        'voucher_id', // <--- Ditambahkan
        'total_amount',
        'discount_amount', // <--- Ditambahkan (Sesuaikan dengan nama kolom DB)
        'status',
        'shipping_address'
    ];

    public function items()
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
