<?php

namespace App\Domains\Catalog\Banner\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this di sini merepresentasikan objek DTO atau Entity yang dikirim
        return [
            'id' => $this->id,
            'store_id' => $this->storeId,
            'image_url' => $this->imageUrl, // Memastikan output JSON konsisten
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];
    }
}
