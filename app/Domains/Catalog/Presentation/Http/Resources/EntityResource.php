<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'categories' => isset($this->categories) ? CategoryResource::collection($this->categories) : null,
        ];
    }
}
