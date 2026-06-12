<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;

final class LogoutUserUseCase
{
    public function execute(User $user, string $type = 'current'): int
    {
        $deletedCount = 1;

        if ($type === 'current') {
            $token = $user->currentAccessToken();
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }
        } elseif ($type === 'other') {
            $currentToken = $user->currentAccessToken();
            $deletedCount = $user->tokens()
                ->when($currentToken, fn($q) => $q->where('id', '!=', $currentToken->id))
                ->delete();
        } elseif ($type === 'all') {
            $deletedCount = $user->tokens()->delete();
        }

        Cache::forget("auth_payload_{$user->id}");

        return (int) $deletedCount;
    }
}
