<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

final class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('productId');
        
        // Dapatkan store_id dari produk induk
        $storeId = DB::table('products')->where('id', $productId)->value('store_id');

        return [
            // PERBAIKAN: Validasi SKU unik dalam scope toko yang sama
            'sku' => [
                'required', 
                'string', 
                'max:100', 
                Rule::unique('product_variants', 'sku')->where(fn ($query) => $query->where('store_id', $storeId))
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'values' => ['nullable', 'array'],
            'values.*.attribute_id' => ['required_with:values', 'integer', 'exists:product_attributes,id'],
            'values.*.value' => ['required_with:values', 'string', 'max:255'],
        ];
    }
}