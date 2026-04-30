<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use Kreait\Firebase\Contract\Auth as FirebaseAuth;

final class FirebaseAuthService
{
    public function __construct(
        private readonly FirebaseAuth $auth,
    ) {}

    public function createPasswordResetLink(string $email): string
    {
        return $this->auth->getPasswordResetLink($email);
    }
}