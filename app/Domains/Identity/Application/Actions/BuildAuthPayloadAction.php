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
        ?string $apiToken = null,
    ): array {
        $user = $user->fresh(['roles']);

        $token = is_string($apiToken) && trim($apiToken) !== ''
            ? trim($apiToken)
            : null;

        $payload = [
            'user' => [
                'id' => (string) $user->id,
                'firebase_uid' => $user->firebase_uid,
                'email' => (string) $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_email_verified' => (bool) $user->is_email_verified,
            ],
            'roles' => $this->users->getRoleNames($user),

            /**
             * Sengaja null.
             * Buyer dan seller bisa aktif bersamaan berdasarkan route/context.
             */
            'active_role' => null,

            'store' => $this->users->getStorePayload($user),
        ];

        if ($token !== null) {
            $payload['token_type'] = 'Bearer';
            $payload['access_token'] = $token;
            $payload['api_token'] = $token;
        }

        return $payload;
    }
}