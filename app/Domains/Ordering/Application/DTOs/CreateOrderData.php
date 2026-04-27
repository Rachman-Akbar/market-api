<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\DTOs;

final readonly class CreateOrderData
{
    public function __construct(
        public int $userId,
        public ShippingAddressData $shippingAddress,
        public ?string $notes = null,
        public ?string $paymentMethod = null,
    ) {
    }

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            shippingAddress: ShippingAddressData::fromArray($data['shipping_address']),
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            paymentMethod: isset($data['payment_method']) ? (string) $data['payment_method'] : null,
        );
    }
}
