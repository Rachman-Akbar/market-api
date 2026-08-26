<?php

declare(strict_types=1);

namespace App\Domains\Identity\Auth\Application\UseCases;

use App\Domains\Identity\User\Domain\Entities\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ChangePasswordUseCase
{
    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak sesuai.'],
            ]);
        }

        if ($currentPassword === $newPassword) {
            throw ValidationException::withMessages([
                'new_password' => ['Password baru tidak boleh sama dengan password saat ini.'],
            ]);
        }

        $user->forceFill([
            'password' => $newPassword,
        ])->save();

        $user->tokens()->delete();
    }
}
