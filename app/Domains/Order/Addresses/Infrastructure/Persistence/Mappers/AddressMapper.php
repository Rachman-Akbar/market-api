<?php

namespace App\Domains\Order\Addresses\Infrastructure\Persistence\Mappers;

use App\Domains\Order\Addresses\Application\DTOs\AddressDTO;

class AddressMapper
{
    public static function fromRequestArray(array $data, ?string $userId = null, ?string $storeId = null): AddressDTO
    {
        return new AddressDTO(
            user_id: $userId,
            store_id: $storeId,
            label: $data['label'],
            recipient_name: $data['recipient_name'],
            phone_number: $data['phone_number'],
            full_address: $data['full_address'],
            city: $data['city'],
            postal_code: $data['postal_code'],
            notes: $data['notes'] ?? null,
            is_primary: $data['is_primary'] ?? false,

            // TAMBAHKAN DUA BARIS INI (Pastikan di AddressDTO juga sudah ada property ini)
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude']
        );
    }

    public static function toEntityArray(AddressDTO $dto): array
    {
        return [
            'user_id' => $dto->user_id,
            'store_id' => $dto->store_id,
            'label' => $dto->label,
            'recipient_name' => $dto->recipient_name,
            'phone_number' => $dto->phone_number,
            'full_address' => $dto->full_address,
            'city' => $dto->city,
            'postal_code' => $dto->postal_code,
            'notes' => $dto->notes,
            'is_primary' => $dto->is_primary,

            // TAMBAHKAN DUA BARIS INI AGAR MASUK KE DATABASE VIA REPOSITORY
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
        ];
    }
}
