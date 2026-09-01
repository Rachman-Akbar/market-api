<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ShowcaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'store_name' => $this->store?->name,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'products_count' => $this->products_count ?? $this->products?->count() ?? 0,
            'products' => $this->whenLoaded('products', fn () => $this->products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'thumbnail' => $product->thumbnail,
                'status' => $product->status,
                'is_active' => (bool) $product->is_active,
                'sort_order' => $product->pivot?->sort_order,
                'price' => (float) ($product->variants?->first()?->price ?? 0),
                'stock' => (int) ($product->variants?->first()?->stock ?? 0),
                'po_stock' => (int) ($product->variants?->first()?->po_stock ?? 0),
                'total_stock' => (int) ($product->variants?->first()?->stock ?? 0) + (int) ($product->variants?->first()?->po_stock ?? 0),
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
