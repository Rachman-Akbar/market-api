<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use Laravel\Sanctum\PersonalAccessToken;

final class BuildAuthPayloadUseCase
{
    public function execute(User $user, ?string $requestedRole = null): array
    {
        $user->loadMissing([
            'roles:id,name',
            'store:id,user_id,name,slug,is_active',
        ]);

        $roles = $user->roleNames();
        $activeRole = $this->activeRole($user, $roles, $requestedRole);
        $store = $user->store;

        return [
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'firebase_uid' => $user->firebase_uid ?? null,
            ],
            'roles' => $roles,
            'active_role' => $activeRole,
            'store' => $store ? [
                'id' => (string) $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'is_active' => (bool) $store->is_active,
            ] : null,
        ];
    }

    private function activeRole(User $user, array $roles, ?string $requestedRole): string
    {
        $requestedRole = strtolower(trim((string) $requestedRole));

        if ($requestedRole !== '' && in_array($requestedRole, $roles, true)) {
            return $requestedRole;
        }

        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            foreach ($token->abilities ?? [] as $ability) {
                if (!is_string($ability) || !str_starts_with($ability, 'active-role:')) {
                    continue;
                }

                $role = strtolower(trim(substr($ability, strlen('active-role:'))));

                if (in_array($role, $roles, true)) {
                    return $role;
                }
            }
        }

        return in_array('buyer', $roles, true) ? 'buyer' : ($roles[0] ?? 'buyer');
    }
}
