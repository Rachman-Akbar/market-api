<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Auth;
use Throwable;

final class FirebaseTokenVerifier
{
    public function __construct(private readonly Auth $auth) {}

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function verify(string $idToken): array
    {
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $claims = $verifiedToken->claims()->all();

            return [
                'uid' => (string) ($claims['sub'] ?? ''),
                'email' => (string) ($claims['email'] ?? ''),
                'name' => isset($claims['name']) ? (string) $claims['name'] : null,
                'picture' => isset($claims['picture']) ? (string) $claims['picture'] : null,
                'email_verified' => (bool) ($claims['email_verified'] ?? false),
                'claims' => $claims,
            ];
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'token' => ['Invalid Firebase ID token.'],
            ]);
        }
    }
}
