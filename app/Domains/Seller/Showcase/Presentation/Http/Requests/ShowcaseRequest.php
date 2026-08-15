<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ShowcaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['required', 'array', 'min:1', 'max:200'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }
}
