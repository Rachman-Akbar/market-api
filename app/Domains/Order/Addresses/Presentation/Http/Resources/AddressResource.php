<?php

namespace App\Domains\Order\Addresses\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,   // Muncul eksplisit (null jika milik toko)
            'store_id'       => $this->store_id,  // Muncul eksplisit (null jika milik buyer)
            'label'          => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone_number'   => $this->phone_number,
            'full_address'   => $this->full_address,
            'city'           => $this->city,
            'postal_code'    => $this->postal_code,
            'is_primary'     => $this->is_primary,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
