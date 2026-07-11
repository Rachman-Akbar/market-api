<?php

declare(strict_types=1);

namespace App\Domains\Order\Voucher\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;

        if ($this->image) {
            $imageUrl = str_starts_with((string) $this->image, 'http')
                ? $this->image
                : Storage::disk('public')->url((string) $this->image);
        }

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'image' => $this->image,
            'imageUrl' => $imageUrl,
            'discountType' => $this->discount_type,
            'discountValue' => $this->discount_value,
            'minSpend' => $this->min_spend,
            'maxDiscount' => $this->max_discount,
            'startsAt' => $this->starts_at?->toDateTimeString(),
            'endsAt' => $this->ends_at?->toDateTimeString(),
            'usageLimit' => $this->usage_limit,
            'usedCount' => $this->used_count,
            'storeId' => $this->store_id,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at?->toDateTimeString(),
        ];
    }
}
