<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Domains\Identity\Application\Actions\IssueApiTokenAction;
use App\Domains\Identity\Application\Actions\BuildAuthPayloadAction;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RegisterWithPasswordAction
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly IssueApiTokenAction $tokens,
        private readonly BuildAuthPayloadAction $payload,
    ) {}

    public function execute(
        string $name,
        string $email,
        string $password
    ): array {
        return DB::transaction(function () use ($name, $email, $password): array {
            $user = $this->users->createWithPassword(
                name: $name,
                email: $email,
                password: $password,
                firebaseUid: null,           // Manual register = tidak pakai Firebase
            );

            // Assign default role
            $this->users->assignRoleByName($user, 'buyer');

            $activeRole = 'buyer';

            $apiToken = $this->tokens->execute(
                user: $user,
                activeRole: $activeRole,
                revokeExistingTokens: true,   // revoke token lama saat register baru
            );

            return $this->payload->execute($user);
        });
    }
}