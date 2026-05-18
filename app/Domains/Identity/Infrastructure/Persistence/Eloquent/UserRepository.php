<?php

declare(strict_types=1);

namespace App\Domains\Identity\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laravel\Sanctum\PersonalAccessToken;
use LogicException;
use RuntimeException;

final class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', strtolower(trim($email)))
            ->first();
    }

    public function findByFirebaseUid(string $firebaseUid): ?User
    {
        return User::query()
            ->where('firebase_uid', trim($firebaseUid))
            ->first();
    }

    public function syncFromFirebase(array $firebaseUser): User
    {
        return DB::transaction(function () use ($firebaseUser): User {
            $firebaseUid = $firebaseUser['uid'] ?? $firebaseUser['sub'] ?? null;
            $email = $firebaseUser['email'] ?? null;

            if (! is_string($firebaseUid) || trim($firebaseUid) === '') {
                throw new InvalidArgumentException('Firebase UID is missing.');
            }

            if (! is_string($email) || trim($email) === '') {
                throw new InvalidArgumentException('Firebase email is missing.');
            }

            $firebaseUid = trim($firebaseUid);
            $email = strtolower(trim($email));

            $name = $firebaseUser['name'] ?? null;
            $avatar = $firebaseUser['picture'] ?? null;
            $isEmailVerified = (bool) ($firebaseUser['email_verified'] ?? true);

            $userByFirebaseUid = $this->findByFirebaseUid($firebaseUid);

            if ($userByFirebaseUid !== null) {
                $userByEmail = $this->findByEmail($email);

                if (
                    $userByEmail !== null
                    && (string) $userByEmail->id !== (string) $userByFirebaseUid->id
                ) {
                    throw new LogicException('Firebase email is already used by another user.');
                }

                $userByFirebaseUid->forceFill([
                    'email' => $email,
                    'name' => $name ?: $userByFirebaseUid->name,
                    'avatar' => $avatar ?: $userByFirebaseUid->avatar,
                    'is_email_verified' => $isEmailVerified,
                ])->save();

                $this->assignRoleByName($userByFirebaseUid, 'buyer');

                return $userByFirebaseUid->refresh();
            }

            $userByEmail = $this->findByEmail($email);

            if ($userByEmail !== null) {
                if (
                    $userByEmail->firebase_uid !== null
                    && $userByEmail->firebase_uid !== $firebaseUid
                ) {
                    throw new LogicException('Email is already linked to another Firebase account.');
                }

                $userByEmail->forceFill([
                    'firebase_uid' => $firebaseUid,
                    'name' => $name ?: $userByEmail->name,
                    'avatar' => $avatar ?: $userByEmail->avatar,
                    'is_email_verified' => $isEmailVerified,
                ])->save();

                $this->assignRoleByName($userByEmail, 'buyer');

                return $userByEmail->refresh();
            }

            $user = User::query()->create([
                'firebase_uid' => $firebaseUid,
                'email' => $email,
                'password' => null,
                'name' => $name,
                'avatar' => $avatar,
                'is_email_verified' => $isEmailVerified,
            ]);

            $this->assignRoleByName($user, 'buyer');

            return $user->refresh();
        });
    }

    public function assignRoleByName(User $user, string $role): void
    {
        $role = $this->normalizeRole($role);

        $roleId = DB::table('roles')
            ->where('name', $role)
            ->value('id');

        if ($roleId === null) {
            throw new RuntimeException("Role [{$role}] does not exist.");
        }

        DB::table('user_roles')->updateOrInsert(
            [
                'user_id' => $user->id,
                'role_id' => $roleId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function getRoleNames(User $user): array
    {
        $user->loadMissing('roles');

        return $user->roles
            ->pluck('name')
            ->map(fn (string $role): string => $this->normalizeRole($role))
            ->unique()
            ->values()
            ->all();
    }

    public function hasRole(User $user, string $role): bool
    {
        $role = $this->normalizeRole($role);

        return $user->roles()
            ->where('roles.name', $role)
            ->exists();
    }

    public function getActiveRoleFromCurrentToken(User $user): ?string
    {
        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return null;
        }

        $abilities = $token->abilities ?? [];

        foreach ($abilities as $ability) {
            if (
                is_string($ability)
                && str_starts_with($ability, 'active-role:')
            ) {
                return str_replace('active-role:', '', $ability);
            }
        }

        return null;
    }

    public function resolveDefaultActiveRole(User $user): ?string
    {
        $roles = $this->getRoleNames($user);

        if (in_array('buyer', $roles, true)) {
            return 'buyer';
        }

        return $roles[0] ?? null;
    }

    public function activateSellerProfile(User $user, int|string $storeId): void
    {
        DB::table('seller_profiles')->updateOrInsert(
            [
                'user_id' => $user->id,
            ],
            [
                'store_id' => $storeId,
                'status' => 'active',
                'verified_at' => now(),
                'suspended_at' => null,
                'rejected_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function hasSellerAccess(User $user): bool
    {
        if (! $this->hasRole($user, 'seller')) {
            return false;
        }

        $sellerProfile = DB::table('seller_profiles')
            ->where('user_id', $user->id)
            ->first();

        if ($sellerProfile !== null) {
            return $sellerProfile->status === 'active';
        }

        return DB::table('stores')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    public function getStorePayload(User $user): ?array
    {
        $sellerProfile = DB::table('seller_profiles')
            ->where('user_id', $user->id)
            ->first();

        $storeQuery = DB::table('stores');

        if ($sellerProfile !== null && $sellerProfile->store_id !== null) {
            $storeQuery->where('id', $sellerProfile->store_id);
        } else {
            $storeQuery->where('user_id', $user->id);
        }

        $store = $storeQuery->first();

        if ($store === null) {
            return null;
        }

        return [
            'id' => (string) $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'status' => (bool) $store->is_active ? 'active' : 'inactive',
            'is_active' => (bool) $store->is_active,
            'seller_status' => $sellerProfile->status ?? null,
        ];
    }

    private function normalizeRole(string $role): string
    {
        return strtolower(trim($role));
    }
}
