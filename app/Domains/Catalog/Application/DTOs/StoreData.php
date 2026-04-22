<?php

namespace App\Domains\Catalog\Application\DTOs;

class StoreData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public bool $isActive,
        public ?StoreDetailData $detail
    ) {}
}