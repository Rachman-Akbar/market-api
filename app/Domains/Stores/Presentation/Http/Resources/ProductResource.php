<?php

declare(strict_types=1);

namespace App\Domains\Stores\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'store_id' => data_get($this->resource, 'store_id'),
            'category_id' => data_get($this->resource, 'category_id'),
            'seller_id' => data_get($this->resource, 'seller_id'),

            'name' => data_get($this->resource, 'name'),
            'slug' => data_get($this->resource, 'slug'),
            'description' => data_get($this->resource, 'description'),

            'price' => (float) data_get($this->resource, 'price', 0),
            'stock' => (int) data_get($this->resource, 'stock', 0),
            'thumbnail' => data_get($this->resource, 'thumbnail'),
            'status' => data_get($this->resource, 'status'),

            'category' => data_get($this->resource, 'category'),
            'store' => data_get($this->resource, 'store'),
            'images' => data_get($this->resource, 'images', []),

            'created_at' => data_get($this->resource, 'created_at'),
            'updated_at' => data_get($this->resource, 'updated_at'),
        ];
    }
}
