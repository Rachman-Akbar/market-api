<?php

namespace App\Domains\Catalog\Domain\Entities;

class Store
{
    public function __construct(
        private string $id,
        private string $name,
        private string $slug,
        private bool $isActive,
        private ?StoreDetail $detail = null
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function detail(): ?StoreDetail
    {
        return $this->detail;
    }
}