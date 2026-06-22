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
                'email' => strtolower(trim($dto->email)),
                'password' => $dto->password ? Hash::make($dto->password) : null,
                'name' => $dto->name ? trim($dto->name) : null,
                'firebase_uid' => $dto->firebaseUid ? trim($dto->firebaseUid) : null,
                'avatar' => $dto->avatar,
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
        // Menggunakan withTrashed() agar data yang sudah ter-soft delete tetap bisa ditemukan untuk di-hard delete
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return false;
        }

        return DB::transaction(function () use ($user): bool {
            // 1. Bersihkan semua relasi role milik user ini dari tabel pivot user_roles
            $user->roles()->detach();

            // 2. Hapus permanen user dari database
            return (bool) $user->forceDelete();
        });
    }

    // ─────────────────────────────
    // Auth & Firebase Logic
    // ─────────────────────────────

    public function syncFromFirebase(array $firebaseUser): User
    {
        return DB::transaction(function () use ($firebaseUser): User {
            $firebaseUid = trim((string) ($firebaseUser['uid'] ?? $firebaseUser['sub'] ?? ''));
            $email = strtolower(trim((string) ($firebaseUser['email'] ?? '')));

            if ($firebaseUid === '' || $email === '') {
                throw new InvalidArgumentException('Data Firebase UID atau Email tidak valid.');
            }

            $name = $firebaseUser['name'] ?? null;
            $avatar = $firebaseUser['picture'] ?? null;
            $isEmailVerified = (bool) ($firebaseUser['email_verified'] ?? true);

            // SKENARIO 1: User sudah login via Google sebelumnya
            $user = $this->findByFirebaseUid($firebaseUid);

            if ($user !== null) {
                $user->forceFill([
                    'email' => $email,
                    'name' => $name ?: $user->name,
                    'avatar' => $avatar ?: $user->avatar,
                    'is_email_verified' => $isEmailVerified,
                ])->save();

                $this->assignRoleByName($user, 'buyer');
                return $user->refresh();
            }

            // SKENARIO 2: User daftar manual, lalu login Google
            $user = $this->findByEmail($email);

            if ($user !== null) {
                if ($user->firebase_uid !== null && $user->firebase_uid !== $firebaseUid) {
                    throw new LogicException('Email ini telah terhubung dengan metode login Google yang berbeda.');
                }

                $user->forceFill([
                    'firebase_uid' => $firebaseUid,
                    'name' => $user->name ?: $name,
                    'avatar' => $user->avatar ?: $avatar,
                    'is_email_verified' => $isEmailVerified,
                ])->save();

                $this->assignRoleByName($user, 'buyer');
                return $user->refresh();
            }

            // SKENARIO 3: User benar-benar baru
            $user = new User();
            $user->id = (string) Str::uuid();

            $user->forceFill([
                'firebase_uid' => $firebaseUid,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'name' => $name ?: Str::before($email, '@'),
                'avatar' => $avatar,
                'is_email_verified' => $isEmailVerified,
            ]);

            $user->save();
            $this->assignRoleByName($user, 'buyer');

            return $user->refresh();
        });
    }

    public function assignRoleByName(User $user, string $role): void
    {
        $roleName = strtolower(trim($role));
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');

        if ($roleId === null) {
            throw new RuntimeException("Role [{$roleName}] tidak ditemukan di database MySQL.");
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

    public function resolveDefaultActiveRole(User $user): ?string
    {
        $roles = $user->roleNames();

        if (in_array('buyer', $roles, true)) {
            return 'buyer';
        }

        return $roles[0] ?? null;
    }

    public function activateSellerProfile(User $user, int|string $storeId): void
    {
        DB::table('seller_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'store_id' => $storeId,
                'status' => 'active',
                'verified_at' => now(),
                'suspended_at' => null,
                'rejected_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function hasSellerAccess(User $user): bool
    {
        $user->loadMissing(['sellerProfile.store']);

        if (!$user->hasRole('seller')) {
            return false;
        }

        $sellerProfile = $user->sellerProfile;

        if ($sellerProfile !== null) {
            return $sellerProfile->status === 'active';
        }

        return $sellerProfile?->store?->is_active === true;
    }


    // Tambahkan di EloquentUserRepository.php

public function registerStore(string $userId, RegisterSellerDTO $dto): int
{
    return DB::transaction(function () use ($userId, $dto): int {
        // 1. Tambah data ke tabel stores
        $storeId = DB::table('stores')->insertGetId([
            'user_id'    => $userId,
            'name'       => $dto->storeName,
            'slug'       => $dto->slug,
            'phone'      => $dto->phone,
            'city'       => $dto->city,
            'province'   => $dto->province,
            'address'    => $dto->address,
            'is_active'  => 1, // Otomatis aktif
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Tambah data default ke tabel store_details
        DB::table('store_details')->insert([
            'store_id'   => $storeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $storeId;
    });
}
}
