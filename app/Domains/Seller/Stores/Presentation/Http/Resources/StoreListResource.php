<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $store = $this->resource;

        return [
            'id' => $store->id(),
            'name' => $store->name(),
            'slug' => $store->slug(),
            'logo' => $store->logo(),
            'is_active' => $store->isActive(),
        ];
    }
}