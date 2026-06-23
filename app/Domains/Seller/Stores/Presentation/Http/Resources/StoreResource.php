<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Domains\Seller\Stores\Domain\Entities\Store $store */
        $store = $this->resource;

        return [
            'id' => $store->id(),
            'user_id' => $store->userId(),
            'name' => $store->name(),
            'slug' => $store->slug(),
            'description' => $store->description(),
            'logo' => $store->logo(),
            'is_active' => $store->isActive(),
            'created_at' => $store->createdAt(),
            'updated_at' => $store->updatedAt(),
        ];
    }
}