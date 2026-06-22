<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\DTOs;

final class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public array $roleIds,
        public ?string $firebaseUid = null,
        public ?string $avatar = null,
        public bool $isEmailVerified = false,
    ) {}

    /**
     * Membuat instance CreateUserDTO dari array (hasil $request->validated())
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            roleIds: $data['role_ids'] ?? $data['roleIds'] ?? [],
            firebaseUid: $data['firebase_uid'] ?? $data['firebaseUid'] ?? null,
            avatar: $data['avatar'] ?? null,
            isEmailVerified: isset($data['is_email_verified']) ? (bool) $data['is_email_verified'] : false,
        );
    }
}


