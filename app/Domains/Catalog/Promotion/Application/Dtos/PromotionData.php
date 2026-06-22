<?php

namespace App\Domains\Catalog\Promotion\Application\Dtos;

class PromotionData
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

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            imageUrl: $data['image_url'],
            mobileImageUrl: $data['mobile_image_url'] ?? null,
            clickAction: $data['click_action'] ?? 'none',
            targetId: $data['target_id'] ?? null,
            targetUrl: $data['target_url'] ?? null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true)
        );
    }

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
