<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Cart\Infrastructure\Persistence\Models\CartModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    use HasApiTokens;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Auto eager load roles.
     * Roles hampir selalu dipakai di auth payload.
     */
    protected $with = [
        'roles:id,name',
    ];

    protected $fillable = [
        'firebase_uid',
        'email',
        'password',
        'name',
        'avatar',
        'is_email_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_email_verified' => 'boolean',
        'password' => 'hashed',
        'deleted_at' => 'datetime',
    ];

    // ─────────────────────────────
    // Relations
    // ─────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles'
        )->withTimestamps();
    }

    public function sellerProfile(): HasOne
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(
            CartModel::class,
            'user_id'
        );
    }

    public function activeCarts(): HasMany
    {
        return $this->hasMany(
            CartModel::class,
            'active_user_id'
        );
    }

    // ─────────────────────────────
    // Optimized Helpers
    // ─────────────────────────────

    /**
     * TANPA query database tambahan.
     */
    public function hasRole(string $role): bool
    {
        $role = strtolower(trim($role));

        return $this->roles
            ->contains(
                fn ($item) =>
                strtolower($item->name) === $role
            );
    }

    public function roleNames(): array
    {
        return $this->roles
            ->pluck('name')
            ->map(
                fn (string $role): string =>
                strtolower(trim($role))
            )
            ->unique()
            ->values()
            ->all();
    }
}