<?php

namespace App\Domains\Catalog\Application\DTOs;

final class BannerData
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $imageUrl,
        public ?string $linkUrl,
        public bool $isActive,
    ) {}
}