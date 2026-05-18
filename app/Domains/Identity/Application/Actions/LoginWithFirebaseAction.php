<?php

declare(strict_types=1);

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;

final class LoginWithFirebaseAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly IssueApiTokenAction $tokens,
        private readonly BuildAuthPayloadAction $payload,
    ) {}

    public function execute(
        array $firebaseUser,
        ?string $deviceName = null,
    ): array {
        $user = $this->users->syncFromFirebase($firebaseUser);

        $apiToken = $this->tokens->execute(
            user: $user,
            deviceName: $deviceName,
            activeRole: 'buyer',
            revokeExistingTokens: false,
        );

        return $this->payload->execute(
            user: $user,
            activeRole: 'buyer',
            apiToken: $apiToken,
        );
    }
}