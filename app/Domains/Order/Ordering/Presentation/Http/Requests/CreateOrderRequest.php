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
            'address_id' => 'required_unless:courier,ambil_sendiri|integer|exists:addresses,id',
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'required|integer',
            'courier' => 'required|string|in:jne,pos,tiki,express,ambil_sendiri',
            'payment_method' => 'required|string|in:midtrans,transfer_manual,cod,tunai_toko',
            'voucher_code' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required_unless' => 'Alamat pengiriman wajib diisi kecuali Anda mengambil sendiri ke toko.',
            'address_id.exists' => 'Alamat pengiriman tidak valid.',
            'cart_item_ids.required' => 'Keranjang belanja tidak boleh kosong.',
            'courier.required' => 'Silakan tentukan metode pengiriman terlebih dahulu.',
            'courier.in' => 'Metode pengiriman opsi kurir tidak valid.',
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak didukung.',
        ];
    }
}
