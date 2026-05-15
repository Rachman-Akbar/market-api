<?php

namespace App\Domains\Identity\Application\Actions;

use App\Models\User;

final class IssueApiTokenAction
{
    public function execute(
        User $user,
        string $activeRole,
        bool $revokeExistingTokens = false,
        bool $revokeCurrentToken = false,
    ): string {
        $activeRole = strtolower(trim($activeRole));

        $currentToken = $user->currentAccessToken();

        /**
         * Untuk login ulang dari device yang sama atau reset semua session,
         * boleh revoke semua token.
         *
         * Tapi untuk switch role, JANGAN pakai ini.
         */
        if ($revokeExistingTokens) {
            $user->tokens()->delete();
        }

        $plainTextToken = $user
            ->createToken("web-session:{$activeRole}", [
                'web',
                "active-role:{$activeRole}",
            ])
            ->plainTextToken;

        /**
         * Untuk switch role:
         * hapus hanya token request saat ini.
         *
         * Device lain tetap aman:
         * - laptop bisa seller
         * - HP tetap buyer
         */
        if (! $revokeExistingTokens && $revokeCurrentToken && $currentToken !== null) {
            $currentToken->delete();
        }

        return $plainTextToken;
    }
}
