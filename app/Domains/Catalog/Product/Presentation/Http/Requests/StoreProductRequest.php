<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

final class StoreProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if (! $this->input('store_id') && $user) {
            // Mengambil store_id asli dari database berdasarkan user_id aktif
            $storeId = DB::table('stores')->where('user_id', $user->id)->value('id');
            if ($storeId) {
                $this->merge([
                    'store_id' => (int) $storeId,
                ]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->input('store_id');

        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],

            // PERBAIKAN: SKU unik dibatasi hanya per toko
            'sku' => [
                'nullable', 
                'string', 
                'max:100', 
                Rule::unique('product_variants', 'sku')->where(fn ($query) => $query->where('store_id', $storeId))
            ],

            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],

            'images' => ['nullable', 'array'],
            'images.*.url' => ['required_with:images', 'string', 'max:2048'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.sort_order' => ['nullable', 'integer'],

            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*.attribute_id' => ['required_with:attribute_values', 'integer', 'exists:product_attributes,id'],
            'attribute_values.*.value' => ['required_with:attribute_values', 'string', 'max:255'],

            'variants' => ['nullable', 'array'],
            'variants.*.sku' => [
                'required_with:variants', 
                'string', 
                'max:100',
                Rule::unique('product_variants', 'sku')->where(fn ($query) => $query->where('store_id', $storeId))
            ],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
        ];
    }
}