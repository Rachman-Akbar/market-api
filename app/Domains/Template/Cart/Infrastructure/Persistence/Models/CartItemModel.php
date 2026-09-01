<?php

declare(strict_types=1);

namespace App\Domains\Template\Cart\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CartItemModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
    ];

    protected $casts = [
        'cart_id' => 'integer',
        'product_variant_id' => 'integer',
        'quantity' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /** @return BelongsTo<CartModel, CartItemModel> */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(CartModel::class, 'cart_id');
    }
}
