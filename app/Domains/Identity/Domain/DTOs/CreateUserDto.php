<?php

declare(strict_types=1);

namespace App\Domains\Identity\Domain\DTOs;

final readonly class CreateUserDTO
{
    /**
     * @param array<int, int> $roleIds
     */
    public function __construct(
        public string $email,
        public ?string $password = null,
        public ?string $name = null,
        public ?string $firebaseUid = null,
        public ?string $avatar = null,
        public array $roleIds = [],
        public bool $isEmailVerified = false
    ) {}
}
