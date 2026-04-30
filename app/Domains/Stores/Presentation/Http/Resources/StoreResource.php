<?php

namespace App\Domains\Stores\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'slug' => $this->slug(),
            'logo_url' => method_exists($this->resource, 'logoUrl') ? $this->logoUrl() : null,
            'banner_url' => method_exists($this->resource, 'bannerUrl') ? $this->bannerUrl() : null,
            'short_description' => method_exists($this->resource, 'shortDescription') ? $this->shortDescription() : null,
            'city' => method_exists($this->resource, 'city') ? $this->city() : null,
            'province' => method_exists($this->resource, 'province') ? $this->province() : null,
            'is_active' => $this->isActive(),
            'details' => method_exists($this->resource, 'details') ? $this->details() : null,
        ];
    }
}
