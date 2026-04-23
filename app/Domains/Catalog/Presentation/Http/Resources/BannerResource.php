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
            'subtitle' => null,
            'image_url' => $this->imageUrl,
            'mobile_image_url' => null,
            'link_type' => null,
            'link_url' => $this->linkUrl,
            'is_active' => $this->isActive,
        ];
    }
}