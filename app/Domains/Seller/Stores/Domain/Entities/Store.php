<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Domain\Entities;

final class Store
{
    public function __construct(
        private int $id,
        private string $userId,
        private string $name,
        private string $slug,
        private ?string $description,
        private ?string $logo,
        private bool $isActive,
        private string $createdAt,
        private string $updatedAt
    ) {}

    public function id(): int { return $this->id; }
    public function userId(): string { return $this->userId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }
    public function logo(): ?string { return $this->logo; }
    public function isActive(): bool { return $this->isActive; }
    public function createdAt(): string { return $this->createdAt; }
    public function updatedAt(): string { return $this->updatedAt; }
}