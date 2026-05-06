<?php

namespace App\Domains\Catalog\Domain\Entities;

final class Category
{
    public function __construct(
        private ?int $id,
        private ?int $catalogGroupId,
        private ?int $parentId,
        private string $name,
        private string $slug,
        private ?string $fullSlug = null,
        private ?string $description = null,
        private ?string $imageUrl = null,
        private ?string $iconUrl = null,
        private ?string $coverImageUrl = null,
        private int $level = 1,
        private int $sortOrder = 0,
        private ?int $productsCount = null,
        private bool $isActive = true,
        private bool $isVisibleInMenu = true,
        private array $children = [],
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function catalogGroupId(): ?int
    {
        return $this->catalogGroupId;
    }

    public function parentId(): ?int
    {
        return $this->parentId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function fullSlug(): ?string
    {
        return $this->fullSlug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function imageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function iconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function coverImageUrl(): ?string
    {
        return $this->coverImageUrl;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function productsCount(): ?int
    {
        return $this->productsCount;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isVisibleInMenu(): bool
    {
        return $this->isVisibleInMenu;
    }

    public function children(): array
    {
        return $this->children;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function changeFullSlug(?string $fullSlug): void
    {
        $this->fullSlug = $fullSlug;
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function changeCatalogGroup(?int $catalogGroupId): void
    {
        $this->catalogGroupId = $catalogGroupId;
    }

    public function moveToParent(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function changeImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function changeIconUrl(?string $iconUrl): void
    {
        $this->iconUrl = $iconUrl;
    }

    public function changeCoverImageUrl(?string $coverImageUrl): void
    {
        $this->coverImageUrl = $coverImageUrl;
    }

    public function changeLevel(int $level): void
    {
        $this->level = $level;
    }

    public function changeSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function changeIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function changeIsVisibleInMenu(bool $isVisibleInMenu): void
    {
        $this->isVisibleInMenu = $isVisibleInMenu;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }
}
