<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Resources;

use App\Domains\Ordering\Domain\Entities\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var OrderItem $item */
        $item = $this->resource;

        return [
            'id' => $item->id(),
            'product_id' => $item->productId(),
            'product_name' => $item->productName(),
            'sku' => $item->sku(),
            'quantity' => $item->quantity(),
            'unit_price' => $item->unitPrice(),
            'subtotal' => $item->subtotal(),
        ];
    }
}
