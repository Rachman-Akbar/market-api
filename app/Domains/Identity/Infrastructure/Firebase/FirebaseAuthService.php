<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use Kreait\Firebase\Contract\Auth as FirebaseAuth;

final class FirebaseAuthService
{
    public function __construct(
        private readonly FirebaseAuth $auth,
    ) {}

    public function createUser(string $email, string $password, string $name): string
    {
        $record = $this->auth->createUser([
            'email' => $email,
            'password' => $password,
            'displayName' => $name,
        ]);

        return $record->uid;
    }

    public function deleteUser(string $firebaseUid): void
    {
        $this->auth->deleteUser($firebaseUid);
    }

    public function createPasswordResetLink(string $email): string
    {
        return $this->auth->getPasswordResetLink($email);
    }
}