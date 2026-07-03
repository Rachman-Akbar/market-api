<?php

namespace App\Domains\Order\Voucher\Application\DTOs;

class VoucherDTO
{
    public function __construct(
        public string $code,
        public string $name,
        public string $discount_type,
        public float $discount_value,
        public float $min_spend,
        public ?float $max_discount,
        public string $starts_at,
        public string $ends_at,
        public int $usage_limit = 0,
        public ?string $store_id = null,
        public bool $is_active = true
    ) {}
}
