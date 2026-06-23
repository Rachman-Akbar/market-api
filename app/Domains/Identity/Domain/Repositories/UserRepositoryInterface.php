<?php

declare(strict_types=1);

namespace App\Domains\Identity\Domain\Repositories;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Features\Auth\Application\DTOs\RegisterSellerDTO;
use App\Domains\Identity\Features\Users\Application\DTOs\CreateUserDTO;
use App\Domains\Identity\Features\Users\Application\DTOs\UpdateUserDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    // --- Data Retrieval ---
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByFirebaseUid(string $firebaseUid): ?User;

    // --- Standard CRUD (Sub-domain Users) ---
    public function create(CreateUserDTO $dto): User;
    public function update(string $id, UpdateUserDTO $dto): ?User;
    public function delete(string $id): bool;

    // --- Auth Specific Logic (Sub-domain Auth) ---
    public function syncFromFirebase(array $firebaseUser): User;
    public function assignRoleByName(User $user, string $role): void;
    public function getActiveRoleFromCurrentToken(User $user): ?string;
    public function resolveDefaultActiveRole(User $user): ?string;

    // --- Seller & Store Management (Cleaned) ---
    /**
     * Mendaftarkan toko baru untuk user dan mengembalikan ID toko.
     */
    public function registerStore(string $userId, RegisterSellerDTO $dto): int;

    /**
     * Memeriksa apakah user memiliki akses seller aktif berdasarkan keberadaan toko.
     */
    public function hasSellerAccess(User $user): bool;
}