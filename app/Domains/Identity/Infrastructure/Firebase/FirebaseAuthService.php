<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use Kreait\Firebase\Auth;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthService
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = Firebase::auth();
    }

    public function createUser(
        string $email,
        string $password,
        string $name
    ): string {
        $user = $this->auth->createUser([
            'email' => $email,
            'password' => $password,
            'displayName' => $name,
            'emailVerified' => false,
        ]);

        return $user->uid;
    }
}