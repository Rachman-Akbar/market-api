<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class SwitchRoleAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly IssueApiTokenAction $tokens,
        private readonly BuildAuthPayloadAction $payload,
    ) {}

    public function execute(User $user, string $role): array
    {
        $role = strtolower(trim($role));

        if (! $this->users->hasRole($user, $role)) {
            throw ValidationException::withMessages([
                'role' => ['Role tersebut tidak dimiliki oleh user ini.'],
            ]);
        }   

        if ($role === 'seller' && ! $this->users->hasSellerAccess($user)) {
            throw ValidationException::withMessages([
                'role' => ['User belum memiliki toko aktif.'],
            ]);
        }

        $apiToken = $this->tokens->execute(
            user: $user,
            activeRole: $role,
            revokeExistingTokens: false,
            revokeCurrentToken: true,
        );

        return $this->payload->execute(
            user: $user->fresh(['roles']),
            activeRole: $role,
            apiToken: $apiToken,
        );
    }
}   
