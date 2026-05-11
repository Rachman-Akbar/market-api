<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseAuthService;
use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RegisterWithPasswordAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly FirebaseAuthService $firebase,
        private readonly IssueApiTokenAction $tokens,
        private readonly BuildAuthPayloadAction $payload,
    ) {}

    public function execute(
        string $name,
        string $email,
        string $password
    ): array {
        $firebaseUid = null;

        try {
            $firebaseUid = $this->firebase->createUser(
                email: $email,
                password: $password,
                name: $name,
            );

            return DB::transaction(function () use (
                $name,
                $email,
                $password,
                $firebaseUid
            ): array {
                $user = $this->users->createWithPassword(
                    name: $name,
                    email: $email,
                    password: $password,
                    firebaseUid: $firebaseUid,
                );

                $this->users->assignRoleByName($user, 'buyer');

                $activeRole = 'buyer';

                $apiToken = $this->tokens->execute(
                    user: $user,
                    activeRole: $activeRole,
                    revokeExistingTokens: false,
                );

                return $this->payload->execute(
                    user: $user,
                    activeRole: $activeRole,
                    apiToken: $apiToken,
                );
            });
        } catch (Throwable $e) {
            if ($firebaseUid !== null) {
                $this->firebase->deleteUser($firebaseUid);
            }

            throw $e;
        }
    }
}