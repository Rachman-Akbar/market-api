<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Domains\Identity\Infrastructure\Firebase\FirebaseAuthService;

final class RegisterWithPasswordAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly FirebaseAuthService $firebase
    ) {}

    public function execute(string $name, string $email, string $password): array
    {
        /**
         * 1️⃣ CREATE USER DI FIREBASE
         */
        $firebaseUid = $this->firebase->createUser(
            $email,
            $password,
            $name
        );

        /**
         * 2️⃣ CREATE USER DI LARAVEL
         */
        $user = $this->users->createWithPassword(
            $name,
            $email,
            $password,
            $firebaseUid
        );

        $this->users->assignRoleByName($user, 'buyer');

        $roles = $this->users->getRoleNames($user->refresh());
        $activeRole = $roles[0];

        $token = $user
            ->createToken('api-token', ['role:' . $activeRole])
            ->plainTextToken;

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