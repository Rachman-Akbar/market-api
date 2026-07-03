<?php

namespace App\Domains\Order\Voucher\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'           => ['required', 'string', 'max:50'],
            'name'           => ['required', 'string', 'max:100'],
            'discount_type'  => ['required', 'in:fixed,percentage'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'min_spend'      => ['required', 'numeric', 'min:0'],
            'max_discount'   => ['nullable', 'numeric', 'min:0'],
            'starts_at'      => ['required', 'date_format:Y-m-d H:i:s'],
            'ends_at'        => ['required', 'date_format:Y-m-d H:i:s', 'after:starts_at'],
            'usage_limit'    => ['required', 'integer', 'min:0'],
            'store_id'       => ['nullable', 'string'], // Untuk sementara diinput manual lewat postman/frontend
            'is_active'      => ['nullable', 'boolean']
        ];
    }
}
