<?php

namespace App\Domains\Stores\Domain\Entities;

final class StoreDetail
{
    public function __construct(
        private ?int $id,
        private int $storeId,
        private ?string $description = null,
        private ?string $address = null,
        private ?string $phone = null,
    ) {}

    public function id(): ?int { return $this->id; }
    public function storeId(): int { return $this->storeId; }
    public function description(): ?string { return $this->description; }
    public function address(): ?string { return $this->address; }
    public function phone(): ?string { return $this->phone; }
}
