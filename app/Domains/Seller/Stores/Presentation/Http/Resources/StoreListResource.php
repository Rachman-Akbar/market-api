<?php

declare(strict_types=1);

namespace App\Domains\Stores\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'province' => $this->province,
            'address' => $this->address,
            'is_active' => (bool) $this->is_active,
            'logo_url' => $this->logo,
            'banner_url' => $this->banner_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}