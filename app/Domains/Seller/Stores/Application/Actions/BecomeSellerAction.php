<?php

declare(strict_types=1);

namespace App\Domains\Stores\Application\Actions;

use App\Domains\Identity\Application\Actions\IssueApiTokenAction;
use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Domains\Stores\Infrastructure\Persistence\Repositories\SellerStoreRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BecomeSellerAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly IssueApiTokenAction $tokens,
        private readonly SellerStoreRepository $stores,
    ) {}

    public function execute(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data): array {
            $existingStore = $this->stores->findByUserId((string) $user->id);

            if ($existingStore !== null) {
                throw ValidationException::withMessages([
                    'store' => ['User already has a store.'],
                ]);
            }

            $store = $this->stores->createFromSellerOnboarding([
                'user_id' => (string) $user->id,
                'name' => $data['store_name'],
                'slug' => $this->generateUniqueSlug($data['store_name']),
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'],
                'email' => $user->email,
                'address' => $data['address'],
                'is_active' => true,
            ]);

            $this->users->assignRoleByName($user, 'seller');

            $this->users->activateSellerProfile(
                user: $user,
                storeId: $store->id,
            );

            $user = $user->fresh(['roles']);

            $roles = $this->users->getRoleNames($user);

            $apiToken = $this->tokens->execute(
                user: $user,
                activeRole: 'seller',
                revokeExistingTokens: false,
            );

            return [
                'user' => [
                    'id' => (string) $user->id,
                    'firebase_uid' => $user->firebase_uid,
                    'email' => $user->email,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'is_email_verified' => (bool) $user->is_email_verified,
                ],
                'roles' => $roles,
                'active_role' => 'seller',
                'store' => [
                    'id' => (string) $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'status' => (bool) $store->is_active ? 'active' : 'inactive',
                    'is_active' => (bool) $store->is_active,
                ],
                'token_type' => 'Bearer',
                'access_token' => $apiToken,

                // Temporary compatibility kalau frontend kamu masih membaca api_token.
                'api_token' => $apiToken,
            ];
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = Str::lower(Str::random(8));
        }

        $slug = $baseSlug;
        $counter = 2;

        while ($this->stores->existsBySlug($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}