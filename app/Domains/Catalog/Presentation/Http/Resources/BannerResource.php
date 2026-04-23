<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id(),
            'title' => $this->title(),
            'subtitle' => method_exists($this->resource, 'subtitle') ? $this->subtitle() : null,
            'image_url' => $this->imageUrl(),
            'mobile_image_url' => method_exists($this->resource, 'mobileImageUrl') ? $this->mobileImageUrl() : null,
            'link_type' => method_exists($this->resource, 'linkType') ? $this->linkType() : null,
            'link_url' => $this->linkUrl(),
            'is_active' => $this->isActive(),
        ];
    }
}