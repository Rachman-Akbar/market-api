<?php

namespace App\Domains\Catalog\Domain\Entities;

class Product
{
    public function __construct(
        public readonly ?string $id,
        public string $sellerId,
        public string $name,
        public string $slug,
        public ?string $description,
        public int $price,
        public string $status,
    ) {}

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }
}