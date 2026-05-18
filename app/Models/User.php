<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Cart\Infrastructure\Persistence\Models\CartModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps();
    }

    public function carts(): HasMany
    {
        return $this->hasMany(CartModel::class, 'user_id');
    }

    public function activeCarts(): HasMany
    {
        return $this->hasMany(CartModel::class, 'active_user_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('roles.name', strtolower(trim($role)))
            ->exists();
    }
}