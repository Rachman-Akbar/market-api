<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use Illuminate\Validation\ValidationException;

final class LoginWithFirebaseAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RegisterUserAction $registerUser,
    ) {}

    /**
     * @param array<string, mixed> $claims
     * @return array{user: array<string, mixed>, roles: list<string>, active_role: string, api_token: string}
     */
    public function execute(array $claims): array
    {
        if (! (bool) ($claims['email_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => ['Email must be verified before login.'],
            ]);
        }

        $firebaseUid = (string) ($claims['uid'] ?? '');

        $user = $this->users->findByFirebaseUid($firebaseUid);
        if ($user === null) {
            $user = $this->registerUser->execute($claims);
        } else {
            $user = $this->users->syncIdentityFields($user, $claims);
        }

        $roles = $this->users->getRoleNames($user);
        if ($roles === []) {
            $this->users->assignRoleByName($user, 'buyer');
            $roles = $this->users->getRoleNames($user->refresh());
        }

        $activeRole = $roles[0];
        $token = $user->createToken('api-token', ['role:' . $activeRole])->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'firebase_uid' => $user->firebase_uid,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_email_verified' => (bool) $user->is_email_verified,
            ],
            'roles' => $roles,
            'active_role' => $activeRole,
            'api_token' => $token,
        ];
    }
}
