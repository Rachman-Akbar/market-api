<?php

namespace App\Domains\Users\Application\Actions;

use App\Models\User;

final class UpsertSellerProfileAction
{
    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $user, array $payload): User
    {
        $user->fill([
            'name' => $payload['name'] ?? $user->name,
            'avatar' => $payload['avatar'] ?? $user->avatar,
        ])->save();

        return $user->refresh();
    }
}
