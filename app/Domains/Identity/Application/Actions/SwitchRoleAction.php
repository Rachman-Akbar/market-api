<?php

declare(strict_types=1);

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\PersonalAccessToken;

final class SwitchRoleAction
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function execute(User $user, string $role): string
    {
        $role = strtolower(trim($role));

        if ($role === '') {
            throw new AuthorizationException('Invalid role.');
        }

        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            throw new AuthenticationException('Missing or invalid access token.');
        }

        if (! $this->users->hasRole($user, $role)) {
            throw new AuthorizationException('Role does not belong to current user.');
        }

        if ($role === 'seller' && ! $this->users->hasSellerAccess($user)) {
            throw new AuthorizationException('Seller access is not active.');
        }

        $abilities = collect($token->abilities ?? [])
            ->filter(fn (mixed $ability): bool => is_string($ability))
            ->reject(fn (string $ability): bool => str_starts_with($ability, 'active-role:'))
            ->push("active-role:{$role}")
            ->unique()
            ->values()
            ->all();

        $token->forceFill([
            'abilities' => $abilities,
        ])->save();

        return $role;
    }
}