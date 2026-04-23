<?php

namespace App\Domains\Catalog\Domain\Entities;

final class Store
{
    public function __construct(
        private ?int $id,
        private string $userId,
        private string $name,
        private string $slug,
        private ?string $description = null,
        private ?string $logo = null,
        private bool $isActive = true,
        private ?StoreDetail $detail = null,
    ) {}

    public function id(): ?int { return $this->id; }
    public function userId(): string { return $this->userId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }
    public function logo(): ?string { return $this->logo; }
    public function isActive(): bool { return $this->isActive; }
    public function detail(): ?StoreDetail { return $this->detail; }

    public function rename(string $name): void { $this->name = $name; }
    public function changeSlug(string $slug): void { $this->slug = $slug; }
    public function changeDescription(?string $description): void { $this->description = $description; }
    public function changeLogo(?string $logo): void { $this->logo = $logo; }
    public function changeIsActive(bool $isActive): void { $this->isActive = $isActive; }
}