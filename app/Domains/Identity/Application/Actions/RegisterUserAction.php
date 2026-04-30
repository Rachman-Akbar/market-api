<?php

namespace App\Domains\Identity\Application\Actions;

use App\Models\User;

final class RegisterUserAction
{
    public function execute(User $user): array
    {
        return [
            'message' => 'User registered successfully.',
            'user' => [
                'id' => $user->id,
                'firebase_uid' => $user->firebase_uid,
                'name' => $user->name,
                'email' => $user->email,
                'is_email_verified' => $user->is_email_verified,
            ],
        ];
    }
}