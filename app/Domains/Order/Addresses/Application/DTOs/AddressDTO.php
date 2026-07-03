<?php

namespace App\Domains\Order\Addresses\Application\DTOs;

class AddressDTO
{
    public function __construct(
        public ?string $user_id,
        public ?string $store_id,
        public string $label,
        public string $recipient_name,
        public string $phone_number,
        public string $full_address,
        public string $city,
        public string $postal_code,
        public ?string $notes,
        public ?float $latitude,
        public ?float $longitude,
        public bool $is_primary = false
    ) {}
}
