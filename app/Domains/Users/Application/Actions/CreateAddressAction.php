<?php

namespace App\Domains\Users\Application\Actions;

use App\Models\Address;

final class CreateAddressAction
{
    /**
     * @param array<string, mixed> $payload
     */
    public function execute(string $userId, array $payload): Address
    {
        return Address::query()->create([
            'user_id' => $userId,
            'label' => $payload['label'],
            'address' => $payload['address'],
            'lat' => $payload['lat'] ?? null,
            'lng' => $payload['lng'] ?? null,
        ]);
    }
}
