<?php

namespace App\Domains\Identity\Domain\Entities;

final class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $firebaseUid,
        public readonly string $email,
        public readonly ?string $name,
        public readonly ?string $avatar,
        public readonly bool $isEmailVerified,
    ) {}
}
