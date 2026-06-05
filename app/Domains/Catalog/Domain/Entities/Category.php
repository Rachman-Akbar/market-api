<?php

namespace App\Domains\Catalog\Domain\Entities;

final class Category
{
    public function __construct(
        private ?int $id,
        private int $catalogGroupId,
        private ?int $parentId,
        private string $name,
        private string $slug,
        private string $fullSlug,
        private int $level,
        private int $sortOrder,
        private bool $isActive,
        private bool $isVisibleInMenu,
        private ?string $imageUrl = null,
        private ?string $iconUrl = null,
        private ?int $productsCount = 0,
        private array $children = []
    ) {}

    public static function createNew(
        int $catalogGroupId,
        ?Category $parent,
        string $name,
        string $slug,
        int $sortOrder = 0,
        bool $isActive = true,
        bool $isVisibleInMenu = true,
        ?string $imageUrl = null,
        ?string $iconUrl = null
    ): self {
        $level = $parent ? $parent->level() + 1 : 1;
        $fullSlug = $parent ? $parent->fullSlug() . '/' . $slug : $slug;

        return new self(
            id: null,
            catalogGroupId: $parent ? $parent->catalogGroupId() : $catalogGroupId,
            parentId: $parent?->id(),
            name: $name,
            slug: $slug,
            fullSlug: $fullSlug,
            level: $level,
            sortOrder: $sortOrder,
            isActive: $isActive,
            isVisibleInMenu: $isVisibleInMenu,
            imageUrl: $imageUrl,
            iconUrl: $iconUrl
        );
    }

    /**
     * Logika Bisnis: Memperbarui data kategori beserta kalkulasi ulang hierarkinya
     */
    public function updateData(array $data, ?Category $newParent): void
    {
        if (array_key_exists('name', $data)) $this->name = $data['name'];
        if (array_key_exists('slug', $data)) $this->slug = $data['slug'];
        if (array_key_exists('sort_order', $data)) $this->sortOrder = (int) $data['sort_order'];
        if (array_key_exists('is_active', $data)) $this->isActive = (bool) $data['is_active'];
        if (array_key_exists('is_visible_in_menu', $data)) $this->isVisibleInMenu = (bool) $data['is_visible_in_menu'];
        if (array_key_exists('image_url', $data)) $this->imageUrl = $data['image_url'];
        if (array_key_exists('icon_url', $data)) $this->iconUrl = $data['icon_url'];

        // Jika ada perubahan parent atau perubahan slug, kalkulasi ulang hierarki
        if ($newParent !== null || array_key_exists('slug', $data) || array_key_exists('parent_id', $data)) {
            $this->parentId = $newParent?->id();
            $this->catalogGroupId = $newParent ? $newParent->catalogGroupId() : ($data['catalog_group_id'] ?? $this->catalogGroupId);
            $this->level = $newParent ? $newParent->level() + 1 : 1;
            $this->fullSlug = $newParent ? $newParent->fullSlug() . '/' . $this->slug : $this->slug;
        }
    }

    // --- GETTERS ---
    public function id(): ?int { return $this->id; }
    public function catalogGroupId(): int { return $this->catalogGroupId; }
    public function parentId(): ?int { return $this->parentId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function fullSlug(): string { return $this->fullSlug; }
    public function imageUrl(): ?string { return $this->imageUrl; }
    public function iconUrl(): ?string { return $this->iconUrl; }
    public function level(): int { return $this->level; }
    public function sortOrder(): int { return $this->sortOrder; }
    public function productsCount(): ?int { return $this->productsCount; }
    public function isActive(): bool { return $this->isActive; }
    public function isVisibleInMenu(): bool { return $this->isVisibleInMenu; }
    public function children(): array { return $this->children; }

    public function setChildren(array $children): void { $this->children = $children; }
    public function hasChildren(): bool { return ! empty($this->children); }
}
