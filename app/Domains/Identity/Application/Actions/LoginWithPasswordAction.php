<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginWithPasswordAction
{
    public function __construct(private readonly UserRepository $users) {}

    /**
     * @return array{user: array<string, mixed>, roles: list<string>, active_role: string, api_token: string}
     */
    public function execute(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
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
