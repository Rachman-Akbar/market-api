<?php

namespace App\Domains\Catalog\Promotion\Domain\Entities;

class Promotion
{
    public function __construct(
        public ?int $id,
        public string $imageUrl,
        public ?string $mobileImageUrl,
        public string $clickAction,
        public ?int $targetId,
        public ?string $targetUrl,
        public int $sortOrder,
        public bool $isActive
    ) {}

    // TAMBAHKAN FUNGSI INI:
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'image_url'        => $this->imageUrl,
            'mobile_image_url' => $this->mobileImageUrl,
            'click_action'     => $this->clickAction,
            'target_id'        => $this->targetId,
            'target_url'       => $this->targetUrl,
            'sort_order'       => $this->sortOrder,
            'is_active'        => $this->isActive,
        ];
    }
}
