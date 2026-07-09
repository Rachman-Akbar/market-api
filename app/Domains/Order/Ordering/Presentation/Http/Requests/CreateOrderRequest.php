<?php

namespace App\Domains\Order\Ordering\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|integer|exists:addresses,id',
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'required|integer',
            'courier' => 'required|string|in:jne,pos,tiki', // Validasi kurir paket starter Komerce
            'payment_method' => 'required|string',
            'voucher_code' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Pilih atau tambahkan alamat pengiriman terlebih dahulu.',
            'address_id.exists' => 'Alamat pengiriman tidak valid.',
            'cart_item_ids.required' => 'Keranjang belanja tidak boleh kosong.',
            'courier.required' => 'Silakan tentukan kurir pengiriman pilihan Anda.',
        ];
    }
}
