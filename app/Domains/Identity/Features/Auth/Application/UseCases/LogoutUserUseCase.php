<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final class LogoutUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(User $user, string $type = 'current'): int
    {
        $deletedCount = 1;

        if ($type === 'current') {
            $this->userRepository->deleteCurrentToken($user);
        } elseif ($type === 'other') {
            // Memanggil fungsi dari repository yang sudah kita perbaiki sebelumnya
            $this->userRepository->logoutOtherDevices($user);
        } elseif ($type === 'all') {
            // Hapus semua token langsung
            $deletedCount = $user->tokens()->delete();
        }

        // Hapus cache payload user agar saat login lagi datanya fresh
        Cache::forget("auth_payload_{$user->id}");

        return (int) $deletedCount;
    }
}