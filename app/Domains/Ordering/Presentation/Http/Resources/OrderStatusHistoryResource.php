<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Resources;

use App\Domains\Ordering\Domain\Entities\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var OrderStatusHistory $history */
        $history = $this->resource;

        return [
            'id' => $history->id(),
            'from_status' => $history->fromStatus()?->value(),
            'to_status' => $history->toStatus()->value(),
            'note' => $history->note(),
            'changed_by' => $history->changedBy(),
            'created_at' => $history->createdAt()?->format(DATE_ATOM),
        ];
    }
}
