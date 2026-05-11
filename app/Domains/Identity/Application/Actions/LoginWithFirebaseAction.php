<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Models\User;

final class LoginWithFirebaseAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly IssueApiTokenAction $tokens,
        private readonly BuildAuthPayloadAction $payload,
    ) {}

    public function execute(User $user): array
    {
        $this->users->assignBuyerRoleIfMissing($user);

        $roles = $this->users->getRoleNames($user->fresh(['roles']));

        $activeRole = in_array('buyer', $roles, true)
            ? 'buyer'
            : ($roles[0] ?? 'buyer');

        $apiToken = $this->tokens->execute(
            user: $user,
            activeRole: $activeRole,
            revokeExistingTokens: false,
        );

        return $this->payload->execute(
            user: $user,
            activeRole: $activeRole,
            apiToken: $apiToken,
        );
    }
}