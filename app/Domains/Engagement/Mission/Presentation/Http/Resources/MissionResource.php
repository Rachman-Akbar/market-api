<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'voucher_id' => $this->voucher_id,
            'voucher' => $this->voucher ? [
                'id' => $this->voucher->id,
                'code' => $this->voucher->code,
                'name' => $this->voucher->name,
                'discount_type' => $this->voucher->discount_type,
                'discount_value' => (float) $this->voucher->discount_value,
            ] : null,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'event_type' => $this->event_type,
            'target_value' => $this->target_value,
            'conditions' => $this->conditions,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'progresses_count' => $this->progresses_count ?? 0,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
