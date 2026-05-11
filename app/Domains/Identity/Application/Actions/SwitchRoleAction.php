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
        if (! $this->users->hasRole($user, $role)) {
            throw ValidationException::withMessages([
                'role' => ['Requested role does not belong to the current user.'],
            ]);
        }

        $apiToken = $this->tokens->execute(
            user: $user,
            activeRole: $role,
            revokeExistingTokens: true,
        );

        return $this->payload->execute(
            user: $user,
            activeRole: $role,
            apiToken: $apiToken,
        );
    }
}