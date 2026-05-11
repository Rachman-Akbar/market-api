<?php

namespace App\Domains\Identity\Application\Actions;

use App\Models\User;

final class IssueApiTokenAction
{
    public function execute(
        User $user,
        string $activeRole,
        bool $revokeExistingTokens = false
    ): string {
        if ($revokeExistingTokens) {
            $user->tokens()->delete();
        }

        return $user
            ->createToken('web-session', [
                'role:' . $activeRole,
                'web',
            ])
            ->plainTextToken;
    }
}