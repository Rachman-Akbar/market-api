<?php

declare(strict_types=1);

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Models\User;

final class BuildAuthPayloadAction
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function execute(
        User $user,
        ?string $activeRole = null,
        ?string $apiToken = null,
    ): array {
        $user = $user->fresh(['roles']);

        $roles = $this->users->getRoleNames($user);

        $activeRole = $activeRole
            ?: $this->users->getActiveRoleFromCurrentToken($user)
            ?: null;

        $payload = [
            'user' => [
                'id' => (string) $user->id,
                'firebase_uid' => $user->firebase_uid,
                'email' => (string) $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_email_verified' => (bool) $user->is_email_verified,
            ],
            'roles' => $roles,
            'active_role' => $activeRole,
            'store' => $this->users->getStorePayload($user),
        ];

        if (is_string($apiToken) && trim($apiToken) !== '') {
            $payload['token_type'] = 'Bearer';
            $payload['access_token'] = $apiToken;
        }

        return $payload;
    }
}