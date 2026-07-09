<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CartModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'active_user_id',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /** @return BelongsTo<User, CartModel> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User, CartModel> */
    public function activeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'active_user_id');
    }

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