<?php

namespace App\Domains\Catalog\Domain\Entities;

class Banner
{
    public function __construct(
        private ?string $id,
        private string $title,
        private string $imageUrl,
        private ?string $linkUrl,
        private bool $isActive,
    ) {}

    public function id(): ?string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function imageUrl(): string
    {
        return $this->imageUrl;
    }

    public function linkUrl(): ?string
    {
        return $this->linkUrl;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
