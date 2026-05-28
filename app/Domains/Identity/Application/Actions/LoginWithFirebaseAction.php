<?php

declare(strict_types=1);

namespace App\Domains\Identity\Application\Actions;

use App\Models\User;
use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class LoginWithFirebaseAction
{
    public function __construct(
        private readonly BuildAuthPayloadAction $payload,
        private readonly UserRepository $users
    ) {}

    public function execute(array $firebaseUser, ?string $deviceName = null): array
    {
        $firebaseUid = $firebaseUser['uid'] ?? null;
        $email       = $firebaseUser['email'] ?? null;
        $name        = $firebaseUser['name'] ?? null;
        $picture     = $firebaseUser['picture'] ?? null;
        $emailVerified = (bool) ($firebaseUser['email_verified'] ?? false);

        if (!$firebaseUid || !$email) {
            throw new \RuntimeException('Firebase UID atau email tidak valid.');
        }

        $email = strtolower(trim($email));
        $deviceName = $deviceName ?? 'marketplace-web';

        $user = DB::transaction(function () use ($firebaseUid, $email, $name, $picture, $emailVerified): User {
            $user = User::query()
                ->where('firebase_uid', $firebaseUid)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                $user = new User();
                if (empty($user->id)) {
                    $user->id = (string) Str::uuid();
                }
            }

            $user->forceFill([
                'firebase_uid'      => $firebaseUid,
                'email'             => $email,
                'name'              => $name ?: Str::before($email, '@'),
                'avatar'            => $picture,
                'is_email_verified' => $emailVerified,
                'password'          => $user->password ?? Hash::make(Str::random(40)),
            ]);

            $user->save();

            // Pastikan role buyer
            $this->users->assignRoleByName($user, 'buyer');

            return $user->fresh(['roles:id,name', 'sellerProfile.store']);
        });

        $token = $user->createToken($deviceName)->plainTextToken;

        $authPayload = $this->payload->execute($user);

        return [
            ...$authPayload,
            'token_type'   => 'Bearer',
            'api_token'    => $token,
            'access_token' => $token,
        ];
    }
}