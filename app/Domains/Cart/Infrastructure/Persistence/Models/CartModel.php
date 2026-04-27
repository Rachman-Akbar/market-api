<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CartModel extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'active_user_id',
        'status',
    ];

    /** @return HasMany<CartItemModel> */
    public function items(): HasMany
    {
        return $this->hasMany(CartItemModel::class, 'cart_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
