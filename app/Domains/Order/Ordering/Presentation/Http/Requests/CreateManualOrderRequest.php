<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:1000'],
            'courier' => ['required', 'string', 'max:50'],
            'service' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:tunai_toko,transfer_manual,cod,manual'],
            'payment_status' => ['nullable', 'string', 'in:paid,unpaid,pending'],
            'status' => ['nullable', 'string', 'in:pending,processing,shipped,received,completed'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
