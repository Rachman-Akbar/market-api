<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class BuildAuthPayloadAction
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function execute(
        User $user,
        ?string $activeRole = null,
        ?string $apiToken = null
    ): array {
        $user = $user->fresh(['roles']);

        $roles = $this->users->getRoleNames($user);

        $activeRole = $activeRole ?: $this->resolveActiveRole($user, $roles);

        $payload = [
            'user' => [
                'id' => $user->id,
                'firebase_uid' => $user->firebase_uid,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_email_verified' => (bool) ($user->is_email_verified ?? false),
            ],
            'roles' => $roles,
            'active_role' => $activeRole,
            'store' => $this->resolveStorePayload($user),
        ];

        if ($apiToken !== null) {
            $payload['api_token'] = $apiToken;
        }

        return $payload;
    }

    private function resolveActiveRole(User $user, array $roles): string
    {
        $token = $user->currentAccessToken();

        foreach ($roles as $role) {
            if ($token !== null && $token->can('role:' . $role)) {
                return $role;
            }
        }

        if (in_array('buyer', $roles, true)) {
            return 'buyer';
        }

        return $roles[0] ?? 'buyer';
    }

    private function resolveStorePayload(User $user): ?array
    {
        /**
         * Store domain kamu sekarang berisi Domain Entity, bukan Eloquent Model.
         * Jadi untuk /me lebih aman pakai read query langsung ke table stores.
         */
        $store = DB::table('stores')
            ->where('user_id', $user->id)
            ->first();

        if ($store === null) {
            return null;
        }

        return [
            'id' => $store->id,
            'user_id' => $store->user_id,
            'name' => $store->name,
            'slug' => $store->slug,
            'description' => $store->description ?? null,
            'short_description' => $store->short_description ?? null,
            'logo' => $store->logo ?? null,
            'is_active' => (bool) ($store->is_active ?? true),
            'status' => (bool) ($store->is_active ?? true) ? 'active' : 'inactive',
        ];
    }
}