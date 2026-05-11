<?php

namespace App\Domains\Identity\Infrastructure\Persistence\Eloquent;

use App\Models\Role;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use LogicException;

final class UserRepository
{
    public function getRoleNames(UserModel $user): array
    {
        return $user
            ->roles()
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }

    public function hasRole(UserModel $user, string $role): bool
    {
        return $user
            ->roles()
            ->where('name', $role)
            ->exists();
    }

    public function assignRoleByName(UserModel $user, string $role): void
    {
        $roleModel = Role::query()->firstOrCreate([
            'name' => $role,
        ]);

        $user->roles()->syncWithoutDetaching([
            $roleModel->id,
        ]);
    }

    public function assignBuyerRoleIfMissing(UserModel $user): void
    {
        if ($user->roles()->exists()) {
            return;
        }

        $this->assignRoleByName($user, 'buyer');
    }

    public function createWithPassword(
        string $name,
        string $email,
        string $password,
        string $firebaseUid
    ): UserModel {
        return UserModel::query()->create([
            'firebase_uid' => $firebaseUid,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_email_verified' => false,
        ]);
    }

    public function findModelByFirebaseUid(string $firebaseUid): ?UserModel
    {
        return UserModel::query()
            ->where('firebase_uid', $firebaseUid)
            ->first();
    }

    public function findModelByEmail(string $email): ?UserModel
    {
        return UserModel::query()
            ->where('email', $email)
            ->first();
    }

    /**
     * Dipakai oleh middleware Firebase.
     *
     * @param array{
     *     uid?: string|null,
     *     email?: string|null,
     *     name?: string|null,
     *     picture?: string|null,
     *     email_verified?: bool|null
     * } $firebaseUser
     */
    public function syncFromFirebase(array $firebaseUser): UserModel
    {
        $firebaseUid = $firebaseUser['uid'] ?? null;
        $email = $firebaseUser['email'] ?? null;
        $name = $firebaseUser['name'] ?? null;
        $avatar = $firebaseUser['picture'] ?? null;
        $isEmailVerified = (bool) ($firebaseUser['email_verified'] ?? false);

        if (! is_string($firebaseUid) || trim($firebaseUid) === '') {
            throw new InvalidArgumentException('Firebase UID is required.');
        }

        if (! is_string($email) || trim($email) === '') {
            throw new InvalidArgumentException('Firebase email is required.');
        }

        return DB::transaction(function () use (
            $firebaseUid,
            $email,
            $name,
            $avatar,
            $isEmailVerified
        ): UserModel {
            $userByUid = UserModel::query()
                ->where('firebase_uid', $firebaseUid)
                ->first();

            $userByEmail = UserModel::query()
                ->where('email', $email)
                ->first();

            if (
                $userByUid !== null &&
                $userByEmail !== null &&
                $userByUid->id !== $userByEmail->id
            ) {
                throw new LogicException(
                    'Firebase UID and email are linked to different local users.'
                );
            }

            if (
                $userByUid === null &&
                $userByEmail !== null &&
                $userByEmail->firebase_uid !== null &&
                $userByEmail->firebase_uid !== $firebaseUid
            ) {
                throw new LogicException(
                    'This email is already linked to a different Firebase UID.'
                );
            }

            $user = $userByUid ?: $userByEmail;

            if ($user === null) {
                $user = UserModel::query()->create([
                    'firebase_uid' => $firebaseUid,
                    'email' => $email,
                    'name' => $name ?: $email,
                    'avatar' => $avatar,
                    'is_email_verified' => $isEmailVerified,
                ]);
            } else {
                $user->forceFill([
                    'firebase_uid' => $user->firebase_uid ?: $firebaseUid,
                    'email' => $email,
                    'name' => $name ?: $user->name,
                    'avatar' => $avatar ?: $user->avatar,
                    'is_email_verified' => $isEmailVerified,
                ])->save();
            }

            $this->assignBuyerRoleIfMissing($user);

            return $user->fresh(['roles']);
        });
    }
}