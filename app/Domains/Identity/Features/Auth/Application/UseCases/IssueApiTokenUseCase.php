<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use Illuminate\Support\Str;

final class IssueApiTokenUseCase
{
    public function execute(
        User $user,
        ?string $deviceName = null,
        string $activeRole = 'buyer',
        bool $revokeExistingTokens = false,
    ): string {
        $activeRole = strtolower(trim($activeRole));

        if ($activeRole === '') {
            $activeRole = 'buyer';
        }

        if ($revokeExistingTokens) {
            $user->tokens()->delete();
        }

        $tokenName = $deviceName;

        if (! is_string($tokenName) || trim($tokenName) === '') {
            $tokenName = 'marketplace-api-' . Str::lower(Str::random(8));
        }

        return $user->createToken($tokenName, [
            'access-api',
            "active-role:{$activeRole}",
        ])->plainTextToken;
    }
}

