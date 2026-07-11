<?php

declare(strict_types=1);

namespace App\Domains\Identity\Infrastructure\Persistence\Repositories;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Features\Auth\Application\DTOs\RegisterSellerDTO;
use App\Domains\Identity\Features\Users\Application\DTOs\CreateUserDTO;
use App\Domains\Identity\Features\Users\Application\DTOs\UpdateUserDTO;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Sanctum\PersonalAccessToken;
use LogicException;
use RuntimeException;

final class EloquentUserRepository implements UserRepositoryInterface
{
    // ─────────────────────────────
    // Standard CRUD
    // ─────────────────────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('roles')->paginate($perPage);
    }

    public function findById(string $id): ?User
    {
        return User::with('roles')->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', strtolower(trim($email)))->first();
    }

    public function findByFirebaseUid(string $firebaseUid): ?User
    {
        return User::query()->where('firebase_uid', trim($firebaseUid))->first();
    }

    public function create(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            $user = new User();
            $user->id = (string) Str::uuid();

            $user->forceFill([
                'email'             => strtolower(trim($dto->email)),
                'password'          => $dto->password ? Hash::make($dto->password) : null,
                'name'              => $dto->name ? trim($dto->name) : null,
                'firebase_uid'      => $dto->firebaseUid ? trim($dto->firebaseUid) : null,
                'avatar'            => $dto->avatar,
                'is_email_verified' => $dto->isEmailVerified,
            ]);

            $user->save();

            if (!empty($dto->roleIds)) {
                $user->roles()->attach($dto->roleIds);
            }

            return $user->refresh();
        });
    }

    public function update(string $id, UpdateUserDTO $dto): ?User
    {
        $user = $this->findById($id);

        if (!$user) {
            return null;
        }

        DB::transaction(function () use ($user, $dto) {
            $updateData = [];

            if ($dto->email !== null) $updateData['email'] = strtolower(trim($dto->email));
            if ($dto->password !== null) $updateData['password'] = Hash::make($dto->password);
            if ($dto->name !== null) $updateData['name'] = trim($dto->name);
            if ($dto->firebaseUid !== null) $updateData['firebase_uid'] = trim($dto->firebaseUid);
            if ($dto->avatar !== null) $updateData['avatar'] = $dto->avatar;
            if ($dto->isEmailVerified !== null) $updateData['is_email_verified'] = $dto->isEmailVerified;

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            if ($dto->roleIds !== null) {
                $user->roles()->sync($dto->roleIds);
            }
        });

        return $user->refresh();
    }

    public function delete(string $id): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        return DB::transaction(function () use ($user): bool {
            $user->tokens()->delete();
            $user->roles()->detach();
            DB::table('stores')->where('user_id', $user->id)->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

            return (bool) $user->delete();
        });
    }

    // ─────────────────────────────
    // Auth & Firebase Logic
    // ─────────────────────────────

    public function deleteCurrentToken(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken || method_exists($token, 'delete')) {
            $token->delete();
        }
    }

    public function syncFromFirebase(array $firebaseUser): User
    {
        return DB::transaction(function () use ($firebaseUser): User {
            $firebaseUid = trim((string) ($firebaseUser['uid'] ?? $firebaseUser['sub'] ?? ''));
            $email = strtolower(trim((string) ($firebaseUser['email'] ?? '')));

            if ($firebaseUid === '' || $email === '') {
                throw new InvalidArgumentException('Data Firebase UID atau email tidak valid.');
            }

            $providerName = trim((string) ($firebaseUser['name'] ?? ''));
            $providerAvatar = trim((string) ($firebaseUser['picture'] ?? ''));
            $isEmailVerified = (bool) ($firebaseUser['email_verified'] ?? true);
            $user = $this->findByFirebaseUid($firebaseUid);

            if ($user !== null) {
                $updates = [
                    'is_email_verified' => $user->is_email_verified || $isEmailVerified,
                ];

                if (!$user->name && $providerName !== '') {
                    $updates['name'] = $providerName;
                }

                if (!$user->avatar && $providerAvatar !== '') {
                    $updates['avatar'] = $providerAvatar;
                }

                $user->forceFill($updates)->save();
                $this->assignRoleByName($user, 'buyer');

                return $user->refresh();
            }

            $user = $this->findByEmail($email);

            if ($user !== null) {
                if ($user->firebase_uid !== null && $user->firebase_uid !== $firebaseUid) {
                    throw new LogicException('Email ini telah terhubung dengan akun Google yang berbeda.');
                }

                $updates = [
                    'firebase_uid' => $firebaseUid,
                    'is_email_verified' => $user->is_email_verified || $isEmailVerified,
                ];

                if (!$user->name && $providerName !== '') {
                    $updates['name'] = $providerName;
                }

                if (!$user->avatar && $providerAvatar !== '') {
                    $updates['avatar'] = $providerAvatar;
                }

                $user->forceFill($updates)->save();
                $this->assignRoleByName($user, 'buyer');

                return $user->refresh();
            }

            $user = new User();
            $user->id = (string) Str::uuid();
            $user->forceFill([
                'firebase_uid' => $firebaseUid,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'name' => $providerName !== '' ? $providerName : Str::before($email, '@'),
                'avatar' => $providerAvatar !== '' ? $providerAvatar : null,
                'is_email_verified' => $isEmailVerified,
            ]);
            $user->save();
            $this->assignRoleByName($user, 'buyer');

            return $user->refresh();
        });
    }

    // ─────────────────────────────
    // Domain Helpers & Access Logic
    // ─────────────────────────────

    public function resolveDefaultActiveRole(User $user): ?string
    {
        $roles = $user->roleNames();

        if (in_array('buyer', $roles, true)) {
            return 'buyer';
        }

        return $roles[0] ?? null;
    }

    /**
     * Cek apakah user memiliki akses seller berdasarkan role
     * dan keberadaan toko yang aktif.
     */
    public function hasSellerAccess(User $user): bool
    {
        // Pastikan punya role seller terlebih dahulu
        if (!$user->hasRole('seller')) {
            return false;
        }

        // Langsung load relasi 'store' (tanpa sellerProfile)
        $user->loadMissing(['store']);

        // Akses diberikan jika toko ada dan statusnya aktif
        return $user->store !== null && (bool) $user->store->is_active === true;
    }

    public function assignRoleByName(User $user, string $role): void
    {
        $roleName = strtolower(trim($role));
        $roleId   = DB::table('roles')->where('name', $roleName)->value('id');

        if ($roleId === null) {
            throw new RuntimeException("Role [{$roleName}] tidak ditemukan di database.");
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_id' => $roleId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function getActiveRoleFromCurrentToken(User $user): ?string
    {
        $token = $user->currentAccessToken();

        if (!$token instanceof PersonalAccessToken) {
            return null;
        }

        foreach ($token->abilities ?? [] as $ability) {
            if (is_string($ability) && str_starts_with($ability, 'active-role:')) {
                return str_replace('active-role:', '', $ability);
            }
        }

        return null;
    }

    // ─────────────────────────────
    // External Domain Logic (Store)
    // ─────────────────────────────

    public function registerStore(string $userId, RegisterSellerDTO $dto): int
    {
        return DB::transaction(function () use ($userId, $dto): int {
            if (DB::table('stores')->where('user_id', $userId)->exists()) {
                throw new RuntimeException('User ini sudah memiliki toko.');
            }

            $slug = $dto->slug;
            $suffix = 1;
            while (DB::table('stores')->where('slug', $slug)->exists()) {
                $slug = $dto->slug . '-' . $suffix++;
            }

            $storeId = DB::table('stores')->insertGetId([
                'user_id' => $userId,
                'name' => $dto->storeName,
                'slug' => $slug,
                'description' => $dto->description,
                'short_description' => $dto->shortDescription,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'city' => $dto->city,
                'province' => $dto->province,
                'address' => $dto->address,
                'logo' => $dto->logo,
                'banner_url' => $dto->bannerUrl,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('store_details')->insert([
                'store_id' => $storeId,
                'owner_name' => $dto->detail['owner_name'] ?? null,
                'owner_phone' => $dto->detail['owner_phone'] ?? null,
                'description' => $dto->detail['description'] ?? null,
                'shipping_policy' => $dto->detail['shipping_policy'] ?? null,
                'return_policy' => $dto->detail['return_policy'] ?? null,
                'open_days' => $dto->detail['open_days'] ?? null,
                'open_time' => $dto->detail['open_time'] ?? null,
                'close_time' => $dto->detail['close_time'] ?? null,
                'whatsapp_url' => $dto->detail['whatsapp_url'] ?? null,
                'instagram_url' => $dto->detail['instagram_url'] ?? null,
                'tiktok_url' => $dto->detail['tiktok_url'] ?? null,
                'website_url' => $dto->detail['website_url'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $storeId;
        });
    }


public function logoutOtherDevices(User $user): int
    {
        $currentTokenId = $user->currentAccessToken()?->id;
        $query = $user->tokens();

        if ($currentTokenId !== null) {
            $query->where('id', '!=', $currentTokenId);
        }

        return $query->delete();
    }
        }
