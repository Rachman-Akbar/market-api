<?php

namespace App\Domains\Stores\Application\Actions;

use App\Domains\Identity\Application\Actions\IssueApiTokenAction;
use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Domains\Stores\Domain\Entities\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BecomeSellerAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly IssueApiTokenAction $tokens,
    ) {}

    public function execute(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $existingStore = Store::query()
                ->where('owner_user_id', $user->id)
                ->first();

            if ($existingStore !== null) {
                throw ValidationException::withMessages([
                    'store' => ['User already has a store.'],
                ]);
            }

            $store = Store::query()->create([
                'owner_user_id' => $user->id,
                'name' => $data['store_name'],
                'slug' => $this->generateUniqueSlug($data['store_name']),
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'],
                'address' => $data['address'],
                'status' => 'active',
            ]);

            $this->users->assignRoleByName($user, 'seller');

            $roles = $this->users->getRoleNames($user->refresh());

            $apiToken = $this->tokens->execute(
                user: $user,
                activeRole: 'seller',
                revokeExistingTokens: true
            );

            return [
                'user' => [
                    'id' => $user->id,
                    'firebase_uid' => $user->firebase_uid,
                    'email' => $user->email,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'is_email_verified' => (bool) $user->is_email_verified,
                ],
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'status' => $store->status,
                ],
                'roles' => $roles,
                'active_role' => 'seller',
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

        while (Store::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}