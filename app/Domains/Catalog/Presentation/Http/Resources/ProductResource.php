<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'categories' => $this->whenLoaded('categories'),
            'images' => $this->images->pluck('url'),
            'stock' => $this->stock->quantity ?? 0,
            'status' => $this->status,
        ];
    }
}
