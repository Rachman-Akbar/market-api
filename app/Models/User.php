<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasUuids;

    protected $table = 'users';

    protected $primaryKey = 'id';

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
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id',
        )->withTimestamps();
    }



public function store(): HasOne
{
    return $this->hasOne(\App\Domains\Stores\Domain\Entities\Store::class, 'owner_user_id');
}
}