<?php

namespace App\Domains\Catalog\Domain\Entities;

class StoreDetail
{
    public function __construct(
        public ?string $logo,
        public ?string $description,
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $phone
    ) {}
}