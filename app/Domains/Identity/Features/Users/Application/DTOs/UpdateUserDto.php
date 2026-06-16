<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\DTOs;

final class UpdateUserDTO
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
        public ?string $name = null,
        public ?string $firebaseUid = null,
        public ?string $avatar = null,
        public ?bool $isEmailVerified = null,
        public ?array $roleIds = null,
    ) {}

    /**
     * Membuat instance DTO dari array (biasanya hasil $request->validated())
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            name: $data['name'] ?? null,
            firebaseUid: $data['firebase_uid'] ?? $data['firebaseUid'] ?? null,
            avatar: $data['avatar'] ?? null,
            isEmailVerified: isset($data['is_email_verified']) ? (bool) $data['is_email_verified'] : null,
            roleIds: $data['role_ids'] ?? $data['roleIds'] ?? null,
        );
    }
}
