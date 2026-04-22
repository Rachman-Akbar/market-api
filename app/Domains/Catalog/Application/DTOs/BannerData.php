<?php

namespace App\Domains\Catalog\Application\DTOs;

class BannerData
{
    public function __construct(
        public string $id,
        public string $title,
        public string $imageUrl,
        public ?string $linkUrl,
        public bool $isActive
    ) {}
}
