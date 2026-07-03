<?php

namespace App\Domains\Order\Addresses\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   // App\Domains\Order\Addresses\Presentation\Http\Requests\StoreAddressRequest.php

public function rules(): array
{
    return [
        'label'          => 'required|string|max:100',
        'recipient_name' => 'required|string|max:255',
        'phone_number'   => 'required|string|max:20',
        'full_address'   => 'required|string',
        'city'           => 'required|string|max:100',
        'postal_code'    => 'required|string|max:20',
        'is_primary'     => 'nullable|boolean',
        'store_id'       => 'nullable|string|max:255',

        // TAMBAHKAN VALIDASI WAJIB DI BAWAH INI
        'latitude'       => 'required|numeric|between:-90,90',
        'longitude'      => 'required|numeric|between:-180,180',
    ];
}
}
