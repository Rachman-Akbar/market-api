<?php

declare(strict_types=1);

namespace App\Domains\Template\Cart\Infrastructure\Persistence\Models;

use App\Domains\Identity\User\Domain\Entities\User;
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
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /** @return BelongsTo<User, CartModel> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<CartItemModel> */
    public function items(): HasMany
    {
        return $this->hasMany(CartItemModel::class, 'cart_id');
    }
}
