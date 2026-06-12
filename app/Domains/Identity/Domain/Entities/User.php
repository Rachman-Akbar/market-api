<?php

declare(strict_types=1);

namespace App\Domains\Identity\Domain\Entities;

use App\Models\SellerProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            'user_roles',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    public function sellerProfile(): HasOne
    {
        return $this->hasOne(SellerProfile::class);
    }

    // ─────────────────────────────
    // Domain Helpers
    // ─────────────────────────────

    public function hasRole(string $role): bool
    {
        $role = strtolower(trim($role));

        return $this->roles->contains(
            fn ($item) => strtolower($item->name) === $role
        );
    }

    /**
     * @return array<int, string>
     */
    public function roleNames(): array
    {
        return $this->roles
            ->pluck('name')
            ->map(fn (string $role): string => strtolower(trim($role)))
            ->unique()
            ->values()
            ->all();
    }
}
