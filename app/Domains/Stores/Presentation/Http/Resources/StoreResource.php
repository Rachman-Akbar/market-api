<?php

declare(strict_types=1);

namespace App\Domains\Stores\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = $this->detail();

        return [
            'id' => $this->id(),
            'user_id' => $this->userId(),
            'name' => $this->name(),
            'slug' => $this->slug(),
            'description' => $this->description(),
            'logo_url' => $this->logo(),
            'is_active' => $this->isActive(),
            'detail' => $detail ? [
                'id' => $detail->id(),
                'store_id' => $detail->storeId(),
                'description' => $detail->description(),
                'address' => $detail->address(),
                'phone' => $detail->phone(),
            ] : null,
        ];
    }
}