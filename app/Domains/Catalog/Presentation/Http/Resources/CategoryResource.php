<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'entity_id' => $this->entity_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'entity' => isset($this->entity) ? new EntityResource($this->entity) : null,
        ];
    }
}
