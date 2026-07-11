<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['nullable', 'required_unless:courier,ambil_sendiri', 'integer', 'exists:addresses,id'],
            'cart_item_ids' => ['required', 'array', 'min:1'],
            'cart_item_ids.*' => ['required', 'integer', 'distinct'],
            'courier' => ['required', 'string', 'max:50'],
            'service' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:midtrans,transfer_manual,cod,tunai_toko'],
            'voucher_code' => ['nullable', 'string', 'max:100'],
        ];
    }
}
