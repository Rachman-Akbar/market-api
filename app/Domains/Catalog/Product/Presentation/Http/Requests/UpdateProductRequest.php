<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

final class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        
        // Ambil store_id dari record produk saat ini di database
        $storeId = DB::table('products')->where('id', $id)->value('store_id');

        return [
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],

            // seller_id DIHAPUS dari validasi

            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            'images' => ['nullable', 'array'],
            'images.*.url' => ['required_with:images', 'string', 'max:2048'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.sort_order' => ['nullable', 'integer'],

            // DISINKRONKAN: Menggunakan nama 'attribute_values'
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*.attribute_id' => ['required_with:attribute_values', 'integer', 'exists:product_attributes,id'],
            'attribute_values.*.value' => ['required_with:attribute_values', 'string', 'max:255'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.sku' => [
                'required_with:variants', 
                'string', 
                'max:100',
                function ($attribute, $value, $fail) use ($storeId) {
                    // Ekstrak index array untuk mendapatkan ID variant (jika ada)
                    preg_match('/variants\.(\d+)\.sku/', $attribute, $matches);
                    $index = isset($matches[1]) ? (int)$matches[1] : null;
                    $variantId = $this->input("variants.{$index}.id");

                    $query = DB::table('product_variants')
                        ->where('sku', $value)
                        ->where('store_id', $storeId);

                    if ($variantId) {
                        $query->where('id', '!=', $variantId);
                    }

                    if ($query->exists()) {
                        $fail("The SKU '{$value}' has already been taken in this store.");
                    }
                }
            ],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
        ];
    }
}