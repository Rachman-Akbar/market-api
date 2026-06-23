<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;

final class BuildAuthPayloadUseCase
{
    public function execute(User $user): array
    {
        // 1. Load relasi esensial secara efisien
        $user->loadMissing([
            'roles:id,name',
            'store:id,user_id,name,slug,is_active',
        ]);

        $roles = $user->roleNames();
        $store = $user->store;

        /**
         * 2. Logika Penentuan 'active_role' untuk Multi-Device:
         * Dikunci selalu ke 'buyer' sebagai default awal sesuai kebutuhan bisnis.
         */
        $defaultActiveRole = 'buyer';

        return [
            'user' => [
                'id'           => (string) $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'avatar'       => $user->avatar,
                'firebase_uid' => $user->firebase_uid ?? null,
            ],
            'roles'       => $roles,        // Mengembalikan list semua akses, misal: ["buyer", "seller"]
            'active_role' => $defaultActiveRole, // Selalu 'buyer' saat pertama kali login
            'store'       => $store ? [
                'id'        => (string) $store->id,
                'name'      => $store->name,
                'slug'      => $store->slug,
                'is_active' => (bool) $store->is_active,
            ] : null,
        ];
    }
}
