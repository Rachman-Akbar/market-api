<?php

declare(strict_types=1);

namespace App\Domains\Cart\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CartResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $items = collect($this->resource['items'] ?? [])
            ->map(static fn (array $item): array => (new CartItemResource($item))->toArray($request))
            ->values()
            ->all();

        return [
            'id' => $this->resource['id'] ?? null,
            'user_id' => $this->resource['user_id'] ?? null,
            'active_user_id' => $this->resource['active_user_id'] ?? null,
            'status' => $this->resource['status'] ?? 'active',
            'total_quantity' => $this->resource['total_quantity'] ?? 0,
            'subtotal' => $this->resource['subtotal'] ?? 0,
            'items' => $items,
        ];
    }
}
