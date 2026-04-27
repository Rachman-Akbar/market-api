<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\ValueObjects;

use App\Domains\Ordering\Application\DTOs\ShippingAddressData;
use InvalidArgumentException;
use JsonSerializable;

final readonly class ShippingAddress implements JsonSerializable
{
    public function __construct(
        public string $recipientName,
        public string $phone,
        public string $addressLine,
        public string $province,
        public string $city,
        public string $district,
        public string $postalCode,
        public ?string $notes = null,
    ) {
        foreach (['recipientName', 'phone', 'addressLine', 'province', 'city', 'district', 'postalCode'] as $property) {
            if (trim((string) $this->{$property}) === '') {
                throw new InvalidArgumentException("Shipping address field [{$property}] cannot be empty.");
            }
        }
    }

    public static function fromData(ShippingAddressData $data): self
    {
        return new self(
            recipientName: $data->recipientName,
            phone: $data->phone,
            addressLine: $data->addressLine,
            province: $data->province,
            city: $data->city,
            district: $data->district,
            postalCode: $data->postalCode,
            notes: $data->notes,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            recipientName: (string) $data['recipient_name'],
            phone: (string) $data['phone'],
            addressLine: (string) $data['address_line'],
            province: (string) $data['province'],
            city: (string) $data['city'],
            district: (string) $data['district'],
            postalCode: (string) $data['postal_code'],
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'recipient_name' => $this->recipientName,
            'phone' => $this->phone,
            'address_line' => $this->addressLine,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'postal_code' => $this->postalCode,
            'notes' => $this->notes,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
