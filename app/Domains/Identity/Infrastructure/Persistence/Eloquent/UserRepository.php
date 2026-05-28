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
    public function createWithPassword(
        string $name,
        string $email,
        string $password,
        ?string $firebaseUid = null
    ): User {
        return User::create([
            'name'              => $name,
            'email'             => strtolower(trim($email)),
            'password'          => $password,
            'firebase_uid'      => $firebaseUid,
            'is_email_verified' => false,
        ]);
    }
   
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

    public function assignRoleByName(User $user, string $role): void
    {
        $role = strtolower(trim($role));

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

    public function getRoleNames(User $user): array
{
    return $user->roleNames();
}

   public function hasRole(User $user, string $role): bool
{
    return $user->hasRole($role);
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
    $user->loadMissing([
        'sellerProfile.store',
    ]);

    if (! $user->hasRole('seller')) {
        return false;
    }

    $sellerProfile = $user->sellerProfile;

    if ($sellerProfile !== null) {
        return $sellerProfile->status === 'active';
    }

    return $sellerProfile?->store?->is_active === true;
}

    private function normalizeRole(string $role): string
    {
        return strtolower(trim($role));
    }
}


