<?php

namespace App\Domains\Catalog\Domain\Entities;

final class Category
{
    public function __construct(
        private ?int $id,
        private ?int $catalogGroupId,
        private string $name,
        private string $slug,
        private ?string $description = null,
        private ?string $imageUrl = null,
        private ?string $coverImageUrl = null,
        private ?int $productsCount = null,
        private bool $isActive = true,
    ) {}

    public function id(): ?int { return $this->id; }
    public function catalogGroupId(): ?int { return $this->catalogGroupId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }
    public function imageUrl(): ?string { return $this->imageUrl; }
    public function coverImageUrl(): ?string { return $this->coverImageUrl; }
    public function productsCount(): ?int { return $this->productsCount; }
    public function isActive(): bool { return $this->isActive; }

    public function rename(string $name): void { $this->name = $name; }
    public function changeSlug(string $slug): void { $this->slug = $slug; }
    public function changeDescription(?string $description): void { $this->description = $description; }
    public function changeCatalogGroup(?int $catalogGroupId): void { $this->catalogGroupId = $catalogGroupId; }
    public function changeImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
    public function changeCoverImageUrl(?string $coverImageUrl): void { $this->coverImageUrl = $coverImageUrl; }
    public function changeIsActive(bool $isActive): void { $this->isActive = $isActive; }
}