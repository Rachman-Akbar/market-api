<?php

namespace App\Domains\Catalog\Domain\Entities;

final class Banner
{
    public function __construct(
        private ?int $id,
        private string $title,
        private string $imageUrl,
        private ?string $linkUrl = null,
        private bool $isActive = true,
    ) {}

    public function id(): ?int { return $this->id; }
    public function title(): string { return $this->title; }
    public function imageUrl(): string { return $this->imageUrl; }
    public function linkUrl(): ?string { return $this->linkUrl; }
    public function isActive(): bool { return $this->isActive; }
}