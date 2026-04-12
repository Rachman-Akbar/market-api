<?php

namespace App\Support\Firebase;

use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials.file'));

        $this->auth = $factory->createAuth();
    }

    public function verifyToken(string $idToken)
    {
        return $this->auth->verifyIdToken($idToken);
    }
}