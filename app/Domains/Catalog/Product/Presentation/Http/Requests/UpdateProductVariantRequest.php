<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

final class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variantId = (int) $this->route('variantId');
        
        // Ambil store_id langsung melalui data varian yang akan diupdate
        $storeId = DB::table('product_variants')->where('id', $variantId)->value('store_id');

        return [
            // PERBAIKAN: Unik per toko dengan pengecualian ID saat ini (Ignore)
            'sku' => [
                'nullable', 
                'string', 
                'max:100', 
                Rule::unique('product_variants', 'sku')
                    ->ignore($variantId)
                    ->where(fn ($query) => $query->where('store_id', $storeId))
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'values' => ['nullable', 'array'],
            'values.*.attribute_id' => ['required_with:values', 'integer', 'exists:product_attributes,id'],
            'values.*.value' => ['required_with:values', 'string', 'max:255'],
        ];
    }
}