<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Presentation\Http\Requests;

use App\Domains\Engagement\Mission\Application\Services\MissionService;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesActiveRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MissionRequest extends FormRequest
{
    use ResolvesActiveRole;

    public function authorize(): bool
    {
        return $this->hasActiveRole($this, 'admin');
    }

    public function rules(): array
    {
        return [
            'voucher_id' => ['nullable', 'integer', 'exists:vouchers,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_type' => ['required', 'string', Rule::in(MissionService::EVENT_TYPES)],
            'target_value' => ['required', 'integer', 'min:1'],
            'conditions' => ['nullable', 'array'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
