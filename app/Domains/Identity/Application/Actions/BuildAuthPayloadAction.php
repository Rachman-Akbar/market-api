<?php

declare(strict_types=1);

namespace App\Domains\Identity\Application\Actions;

use App\Models\User;

final class BuildAuthPayloadAction
{
    public function execute(User $user): array
    {
        $user->loadMissing([
            'roles:id,name',
            'sellerProfile.store:id,user_id,name,slug,is_active',
        ]);

        $roles = $user->roleNames();

        $store = $user->sellerProfile?->store;

        return [
            'user' => [
                'id'      => (string) $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'avatar'  => $user->avatar,
                'firebase_uid' => $user->firebase_uid ?? null,
            ],
            'roles'       => $roles,
            'active_role' => $roles[0] ?? 'buyer',
            'store'       => $store ? [
                'id'        => (string) $store->id,
                'name'      => $store->name,
                'slug'      => $store->slug,
                'is_active' => (bool) $store->is_active,
            ] : null,
        ];
    }
}