<?php
declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\DTOs;

final readonly class RegisterSellerDTO
{
    public function __construct(
        public string $storeName,
        public string $slug,
        public ?string $phone = null,
        public ?string $city = null,
        public ?string $province = null,
        public ?string $address = null
    ) {}
}