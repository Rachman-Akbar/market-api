<?php

namespace App\Domains\Identity\Domain\Entities;

final class User
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $firebaseUid,
        public readonly string $email,
        public readonly ?string $name = null,
        public readonly ?string $avatar = null,
        public readonly bool $isEmailVerified = false,
    ) {}
}