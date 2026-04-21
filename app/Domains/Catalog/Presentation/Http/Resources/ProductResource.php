<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
public function toArray($request): array
{
    return [
        'id' => $this->resource->id(),
        'seller_id' => $this->resource->sellerId(),
        'name' => $this->resource->name(),
        'slug' => $this->resource->slug(),
        'description' => $this->resource->description(),
        'price' => $this->resource->price(),
        'status' => $this->resource->status(),
    ];
}
}
