<?php

namespace App\Domains\Catalog\Domain\Entities;

final class CatalogGroup
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $slug,
        private bool $isActive = true,
        private array $categories = [],
    ) {}

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function isActive(): bool { return $this->isActive; }
    public function categories(): array { return $this->categories; }
    public function rename(string $name): void { $this->name = $name; }
    public function changeSlug(string $slug): void { $this->slug = $slug; }
    public function changeIsActive(bool $isActive): void { $this->isActive = $isActive; }
}


