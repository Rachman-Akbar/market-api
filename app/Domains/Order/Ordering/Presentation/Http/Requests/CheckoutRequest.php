<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'array'],
            'shipping_address.recipient_name' => ['required', 'string', 'max:150'],
            'shipping_address.phone' => ['required', 'string', 'max:30'],
            'shipping_address.address_line' => ['required', 'string', 'max:500'],
            'shipping_address.province' => ['required', 'string', 'max:100'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.district' => ['nullable', 'string', 'max:100'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_address.courier_note' => ['nullable', 'string', 'max:500'],
            'shipping_address.notes' => ['nullable', 'string', 'max:500'],

            'payment_method' => [
                'required',
                'string',
                Rule::in([
                    'manual_transfer',
                    'bank_transfer',
                    'cod',
                    'midtrans',
                ]),
            ],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
