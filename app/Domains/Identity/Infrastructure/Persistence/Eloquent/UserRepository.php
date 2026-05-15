<?php

namespace App\Domains\Identity\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

final class UserRepository
{
    public function syncFromFirebase(array $firebaseUser): User
    {
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
        $avatarUrl = $firebaseUser['picture'] ?? null;
        $emailVerified = (bool) ($firebaseUser['email_verified'] ?? false);

        return DB::transaction(function () use (
            $firebaseUid,
            $email,
            $name,
            $avatarUrl,
            $emailVerified
        ): User {
            $user = null;

            if (Schema::hasColumn('users', 'firebase_uid')) {
                $user = User::query()
                    ->where('firebase_uid', $firebaseUid)
                    ->first();
            }

            if (! $user) {
                $user = User::query()
                    ->where('email', $email)
                    ->first();
            }

            if ($user) {
                if (
                    Schema::hasColumn('users', 'firebase_uid') &&
                    $user->firebase_uid !== null &&
                    $user->firebase_uid !== $firebaseUid
                ) {
                    throw new LogicException(
                        'Email is already linked to another Firebase account.'
                    );
                }

                $payload = [
                    'email' => $email,
                ];

                if (Schema::hasColumn('users', 'firebase_uid')) {
                    $payload['firebase_uid'] = $firebaseUid;
                }

                if (
                    Schema::hasColumn('users', 'name') &&
                    is_string($name) &&
                    trim($name) !== ''
                ) {
                    $payload['name'] = trim($name);
                }

                if (
                    Schema::hasColumn('users', 'avatar_url') &&
                    is_string($avatarUrl) &&
                    trim($avatarUrl) !== ''
                ) {
                    $payload['avatar_url'] = trim($avatarUrl);
                }

                if (Schema::hasColumn('users', 'photo_url')) {
                    $payload['photo_url'] = is_string($avatarUrl)
                        ? trim($avatarUrl)
                        : null;
                }

                if (Schema::hasColumn('users', 'is_email_verified')) {
                    $payload['is_email_verified'] = $emailVerified;
                }

                if (Schema::hasColumn('users', 'email_verified_at')) {
                    $payload['email_verified_at'] = $emailVerified
                        ? now()
                        : null;
                }

                $user->forceFill($payload)->save();

                $this->assignRole($user, 'buyer');

                return $user->refresh();
            }

            $user = new User();

            $payload = [
                'email' => $email,
            ];

            if (Schema::hasColumn('users', 'firebase_uid')) {
                $payload['firebase_uid'] = $firebaseUid;
            }

            if (Schema::hasColumn('users', 'name')) {
                $payload['name'] = is_string($name) && trim($name) !== ''
                    ? trim($name)
                    : $email;
            }

            if (
                Schema::hasColumn('users', 'avatar_url') &&
                is_string($avatarUrl) &&
                trim($avatarUrl) !== ''
            ) {
                $payload['avatar_url'] = trim($avatarUrl);
            }

            if (
                Schema::hasColumn('users', 'photo_url') &&
                is_string($avatarUrl) &&
                trim($avatarUrl) !== ''
            ) {
                $payload['photo_url'] = trim($avatarUrl);
            }

            if (Schema::hasColumn('users', 'is_email_verified')) {
                $payload['is_email_verified'] = $emailVerified;
            }

            if (Schema::hasColumn('users', 'email_verified_at')) {
                $payload['email_verified_at'] = $emailVerified
                    ? now()
                    : null;
            }

            if (Schema::hasColumn('users', 'password')) {
                $payload['password'] = bcrypt(str()->random(40));
            }

            $user->forceFill($payload)->save();

            $this->assignRole($user, 'buyer');

            return $user->refresh();
        });
    }

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

    public function assignBuyerRoleIfMissing(User $user): void
{
    $this->assignRole($user, 'buyer');
}

    public function assignRole(User $user, string $role): void
    {
        $role = strtolower(trim($role));

        $roleId = DB::table('roles')
            ->where('name', $role)
            ->value('id');

        if ($roleId === null) {
            throw new RuntimeException("Role {$role} not found.");
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