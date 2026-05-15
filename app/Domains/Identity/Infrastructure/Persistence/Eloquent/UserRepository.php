<?php

namespace App\Domains\Identity\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UserRepository
{
    public function getRoleNames(User $user): array
    {
        return $user
            ->roles()
            ->pluck('name')
            ->values()
            ->all();
    }

    public function hasRole(User $user, string $role): bool
    {
        $role = strtolower(trim($role));

        return $user
            ->roles()
            ->where('name', $role)
            ->exists();
    }

    public function hasActiveStore(User $user): bool
    {
        return DB::table('stores')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    public function hasSellerAccess(User $user): bool
    {
        return $this->hasRole($user, 'seller')
            && $this->hasActiveStore($user);
    }

    public function assignRole(User $user, string $role): void
    {
        $role = strtolower(trim($role));

        $roleId = DB::table('roles')
            ->where('name', $role)
            ->value('id');

        if ($roleId === null) {
            throw new \RuntimeException("Role {$role} not found.");
        }

        $exists = DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->exists();

        if (! $exists) {
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
