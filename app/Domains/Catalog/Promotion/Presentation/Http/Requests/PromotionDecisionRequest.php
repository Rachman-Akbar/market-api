<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PromotionDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => [$this->routeIs('promotions.reject') ? 'required' : 'nullable', 'string', 'max:1000'],
        ];
    }
}
