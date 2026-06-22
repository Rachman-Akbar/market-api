<?php

namespace App\Domains\Catalog\Banner\Domain\Entities;

class Banner
{
    public function __construct(
        public ?int $id,
        public int $storeId,
        public string $imageUrl,
        public int $sortOrder,
        public bool $isActive
    ) {}

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
