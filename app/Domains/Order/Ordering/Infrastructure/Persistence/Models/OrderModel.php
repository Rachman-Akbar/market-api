<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $guarded = ['id'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_discount_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'seller_net' => 'decimal:2',
        'preorder_release_at' => 'datetime',
        'booking_expires_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrderModel::class, 'order_id');
    }
}
