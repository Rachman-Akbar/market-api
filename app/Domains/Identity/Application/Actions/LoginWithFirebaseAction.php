<?php

declare(strict_types=1);

namespace App\Domains\Identity\Application\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class LoginWithFirebaseAction
{
    public function __construct(
        private readonly BuildAuthPayloadAction $payload,
    ) {}

    public function execute(array $firebaseUser, ?string $deviceName = null): array
    {
        $firebaseUid = $firebaseUser['uid'] ?? null;
        $email = $firebaseUser['email'] ?? null;
        $name = $firebaseUser['name'] ?? null;
        $picture = $firebaseUser['picture'] ?? null;
        $emailVerified = (bool) ($firebaseUser['email_verified'] ?? false);

        if (! is_string($firebaseUid) || trim($firebaseUid) === '') {
            throw new \RuntimeException('Firebase UID kosong.');
        }

        if (! is_string($email) || trim($email) === '') {
            throw new \RuntimeException('Email Firebase kosong.');
        }

        $email = trim(strtolower($email));
        $deviceName = $deviceName ?: 'marketplace-web';

        $user = DB::transaction(function () use (
            $firebaseUid,
            $email,
            $name,
            $picture,
            $emailVerified
        ): User {
            /** @var User|null $user */
            $user = User::query()
                ->where('firebase_uid', $firebaseUid)
                ->orWhere('email', $email)
                ->first();

            if (! $user instanceof User) {
                $user = new User();

                if (! $user->getKey()) {
                    $user->id = (string) Str::uuid();
                }

                if (Schema::hasColumn('users', 'password')) {
                    $user->password = Hash::make(Str::random(40));
                }
            }

            $payload = [
                'firebase_uid' => $firebaseUid,
                'email' => $email,
                'name' => is_string($name) && trim($name) !== ''
                    ? $name
                    : Str::before($email, '@'),
            ];

            if (Schema::hasColumn('users', 'avatar_url')) {
                $payload['avatar_url'] = $picture;
            }

            if (Schema::hasColumn('users', 'photo_url')) {
                $payload['photo_url'] = $picture;
            }

            if (Schema::hasColumn('users', 'is_email_verified')) {
                $payload['is_email_verified'] = $emailVerified;
            }

            if (Schema::hasColumn('users', 'email_verified_at') && $emailVerified) {
                $payload['email_verified_at'] = $user->email_verified_at ?? now();
            }

            $user->forceFill($payload);
            $user->save();

            return $user;
        });

        $token = $user->createToken($deviceName)->plainTextToken;

        $authPayload = $this->payload->execute(user: $user);

        return [
            ...$authPayload,
            'token_type' => 'Bearer',
            'api_token' => $token,
            'access_token' => $token,
        ];
    }
}