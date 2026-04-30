<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use Kreait\Firebase\Contract\Auth as FirebaseAuth;

final class FirebaseTokenVerifier
{
    public function __construct(
        private readonly FirebaseAuth $auth,
    ) {}

    public function verify(string $idToken): array
    {
        $verifiedToken = $this->auth->verifyIdToken($idToken);

        $claims = $verifiedToken->claims();

        return [
            'uid' => $claims->get('sub'),
            'email' => $claims->get('email'),
            'email_verified' => (bool) $claims->get('email_verified', false),
            'name' => $claims->get('name'),
            'picture' => $claims->get('picture'),
            'claims' => $claims->all(),
        ];
    }
}