<?php

namespace App\Domains\Catalog\Banner\Application\Dtos;

class BannerData
{
    public function __construct(
        public ?int $id,
        public int $storeId,
        public string $imageUrl,
        public int $sortOrder,
        public bool $isActive
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            storeId: (int) ($data['store_id']),
            imageUrl: $data['image_url'],
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true)
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'store_id'   => $this->storeId,
            'image_url'  => $this->imageUrl,
            'sort_order' => $this->sortOrder,
            'is_active'  => $this->isActive,
        ];
    }
}
