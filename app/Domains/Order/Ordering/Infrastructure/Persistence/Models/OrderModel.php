<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'currency',
        'subtotal',
        'shipping_cost',
        'discount_total',
        'tax_total',
        'grand_total',
        'shipping_address',
        'notes',
        'payment_method',
    ];
    
    protected $casts = [
        'shipping_address' => 'array',
        'source_cart_item_ids' => 'array',
        'midtrans_payload' => 'array',
        'payment_instructions' => 'array',
        'paid_at' => 'datetime',
        'payment_expires_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistoryModel::class, 'order_id')->oldest('id');
    }
}
