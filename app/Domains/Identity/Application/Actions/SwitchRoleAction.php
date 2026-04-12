<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class SwitchRoleAction
{
    public function __construct(private readonly UserRepository $users) {}

    /**
     * @return array{user: array<string, mixed>, roles: list<string>, active_role: string, api_token: string}
     */
    public function execute(User $user, string $role): array
    {
        if (! $this->users->hasRole($user, $role)) {
            throw ValidationException::withMessages([
                'role' => ['Requested role does not belong to the current user.'],
            ]);
        }

        $user->tokens()->delete();

        $roles = $this->users->getRoleNames($user);
        $token = $user->createToken('api-token', ['role:' . $role])->plainTextToken;

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
            'active_role' => $role,
            'api_token' => $token,
        ];
    }
}
