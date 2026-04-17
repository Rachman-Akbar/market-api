<?php

namespace App\Domains\Identity\Infrastructure\Persistence\Eloquent;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function findByFirebaseUid(string $firebaseUid): ?User
    {
        return User::query()->where('firebase_uid', $firebaseUid)->first();
    }

    /**
     * @param array<string, mixed> $claims
     */
public function createFromFirebaseClaims(array $claims): User
{
    $existing = $this->findByEmail($claims['email']);

    if ($existing) {
        return $this->syncIdentityFields($existing, $claims);
    }

    return User::query()->create([
        'firebase_uid' => $claims['uid'],
        'email' => $claims['email'],
        'name' => $claims['name'] ?? null,
        'avatar' => $claims['picture'] ?? null,
        'is_email_verified' => (bool) ($claims['email_verified'] ?? false),
    ]);
}

public function syncIdentityFields(User $user, array $claims): User
{
    $user->forceFill([
        'firebase_uid' => $claims['uid'] ?? $user->firebase_uid,
        'email' => $claims['email'] ?? $user->email,
        'name' => $claims['name'] ?? $user->name,
        'avatar' => $claims['picture'] ?? $user->avatar,
        'is_email_verified' => (bool) ($claims['email_verified'] ?? false),
    ])->save();

    return $user->refresh();
} 
 public function createWithPassword(
    string $name,
    string $email,
    string $password,
    string $firebaseUid
): User {

    $payload = [
        'firebase_uid' => $firebaseUid,
        'email' => $email,
        'name' => $name,
        'password' => Hash::make($password),
        'is_email_verified' => false,
    ];

    if (Schema::hasColumn('users', 'role')) {
        $payload['role'] = 'buyer';
    }

    if (Schema::hasColumn('users', 'id')) {
        $idColumnType = Schema::getColumnType('users', 'id');

        if (! str_contains($idColumnType, 'int')) {
            $payload['id'] = (string) Str::uuid();
        }
    }

    /** @var User $user */
    $user = User::query()->create($payload);

    return $user->refresh();
}

    public function assignRoleByName(User $user, string $roleName): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_roles')) {
            return;
        }

        $role = Role::query()->where('name', $roleName)->firstOrFail();

        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }
    }

    /**
     * @return list<string>
     */
    public function getRoleNames(User $user): array
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_roles')) {
            $fallbackRole = (string) ($user->getAttribute('last_active_role') ?? $user->getAttribute('role') ?? 'buyer');

            return [$fallbackRole];
        }

        /** @var Collection<int, Role> $roles */
        $roles = $user->roles()->get(['roles.name']);

        return $roles->pluck('name')->all();
    }

    public function hasRole(User $user, string $roleName): bool
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_roles')) {
            $currentRole = (string) ($user->getAttribute('last_active_role') ?? $user->getAttribute('role') ?? 'buyer');

            return $currentRole === $roleName;
        }

        return $user->roles()->where('name', $roleName)->exists();
    }
}
