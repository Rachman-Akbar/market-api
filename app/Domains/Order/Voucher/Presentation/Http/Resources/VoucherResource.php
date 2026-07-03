<?php

namespace App\Domains\Order\Voucher\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'discountType'  => $this->discount_type,
            'discountValue' => $this->discount_value,
            'minSpend'      => $this->min_spend,
            'maxDiscount'   => $this->max_discount,
            'startsAt'      => $this->starts_at->toDateTimeString(),
            'endsAt'        => $this->ends_at->toDateTimeString(),
            'usageLimit'    => $this->usage_limit,
            'usedCount'     => $this->used_count,
            'storeId'       => $this->store_id,
            'isActive'      => $this->is_active,
            'createdAt'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
