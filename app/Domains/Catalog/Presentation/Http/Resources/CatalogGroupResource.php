<?php

namespace App\Domains\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Catalog\Domain\Entities\CatalogGroup;

final class CatalogGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var CatalogGroup $group */
        $group = $this->resource;

        return [
            'id'              => $group->id(),
            'name'            => $group->name(),
            'slug'            => $group->slug(),
            'description'     => $group->description(),
            'image_url'       => $group->imageUrl(),
            'cover_image_url' => $group->coverImageUrl(),
            'is_active'       => $group->isActive(),
            
            // Ini yang paling penting
            'categories' => CategoryResource::collection(
                $group->categories()
            ),
        ];
    }
}