<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
           'image' => $this->imageUrl,
            'link' => $this->linkUrl,
            'is_active' => $this->isActive,
        ];
    }
}
