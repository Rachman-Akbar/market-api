<?php

namespace App\Domains\Catalog\Product\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Domain\Entities\ProductAttribute;

class ProductAttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var ProductAttribute $attribute */
        $attribute = $this->resource;

        return [
            'id' => $attribute->id(),
            'name' => $attribute->name(),
            'slug' => $attribute->slug(),
            'type' => $attribute->type(),
            'is_active' => $attribute->isActive(),
        ];
    }
}


