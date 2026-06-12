<?php

declare(strict_types=1);

namespace App\Domains\Identity\Domain\DTOs;

final readonly class UpdateUserDTO
{
    /**
     * @param array<int, int>|null $roleIds
     */
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
        public ?string $name = null,
        public ?string $firebaseUid = null,
        public ?string $avatar = null,
        public ?bool $isEmailVerified = null,
        public ?array $roleIds = null
    ) {}
}
