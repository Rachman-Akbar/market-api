<?php

declare(strict_types=1);

namespace App\Domains\Cart\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'min:1', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
