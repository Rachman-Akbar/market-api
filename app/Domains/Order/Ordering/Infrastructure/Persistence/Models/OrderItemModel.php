<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemModel extends Model
{
    protected $table = 'order_items';

    // TAMBAHKAN 'store_id' ke dalam array fillable di bawah ini
    protected $fillable = [
        'order_id',
        'product_id',
        'store_id', // <--- WAJIB ADA DI SINI
        'product_name',
        'sku',
        'price',
        'quantity'
    ];
}
