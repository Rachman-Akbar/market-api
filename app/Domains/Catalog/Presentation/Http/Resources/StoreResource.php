<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'slug' => $this->slug(),
            'is_active' => $this->isActive(),
        ];
    }
}