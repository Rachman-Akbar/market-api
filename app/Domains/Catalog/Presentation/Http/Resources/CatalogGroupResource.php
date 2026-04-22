<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CatalogGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'slug' => $this->slug(),
            'description' => $this->description(),
        ];
    }
}
