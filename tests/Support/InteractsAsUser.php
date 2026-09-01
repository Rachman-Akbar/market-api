<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domains\Identity\User\Domain\Entities\Role;
use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Support\Str;

/**
 * Helpers to build realistic users/stores for HTTP feature tests.
 *
 * The app's auth middleware (`active.role`, `role`, `seller.store.available`)
 * inspects the Sanctum token `abilities` for an `active-role:{role}` entry and
 * requires a non-suspended store for sellers, so these helpers mirror the real
 * token issued by IssueApiTokenUseCase and the real seller store setup.
 */
trait InteractsAsUser
{
    private function makeRole(string $name): Role
    {
        return Role::query()->firstOrCreate(
            ['name' => $name],
            ['description' => $name, 'is_active' => true],
        );
    }

    private function makeUser(array $attributes = [], array $roles = ['buyer']): User
    {
        $user = User::query()->create(array_merge([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'is_email_verified' => true,
            'is_active' => true,
            'banned_at' => null,
        ], $attributes));

        $roleIds = array_map(fn (string $role): int => $this->makeRole($role)->id, $roles);
        $user->allRoles()->syncWithoutDetaching($roleIds);

        return $user->refresh();
    }

    private function tokenFor(User $user, string $activeRole): string
    {
        return $user->createToken('test', [
            'access-api',
            "active-role:{$activeRole}",
        ])->plainTextToken;
    }

    /**
     * Authenticate as a user with a given active role, returning them.
     */
    private function actingAsRole(string $role, array $attributes = []): User
    {
        $user = $this->makeUser($attributes, [$role]);
        $this->withToken($this->tokenFor($user, $role));

        return $user;
    }

    /**
     * Create a (approved by default) store owned by the given user.
     */
    private function makeStore(User $user, array $attributes = []): StoreModel
    {
        return StoreModel::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Store',
            'slug' => 'test-store-'.strtolower((string) Str::random(6)),
            'phone' => '08123456789',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'address' => 'Jl. Test No. 1',
            'status' => 'approved',
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Create a seller with an approved store and authenticate as them.
     */
    private function actingAsSeller(array $userAttributes = [], array $storeAttributes = []): array
    {
        $seller = $this->actingAsRole('seller', $userAttributes);
        $store = $this->makeStore($seller, $storeAttributes);
        $seller->unsetRelation('store');

        return [$seller, $store];
    }
}
