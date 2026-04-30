<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'firebase_uid',
        'email',
        'name',
        'avatar',
        'is_email_verified',
    ];

    protected $casts = [
        'is_email_verified' => 'boolean',
    ];

    protected $hidden = [
        'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        );
    }
}