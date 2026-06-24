<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if (! $this->input('store_id') && $user && isset($user->store_id)) {
            $this->merge([
                'store_id' => $user->store_id,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],

            // PERBAIKAN: SKU divalidasi ke tabel product_variants
            'sku' => ['nullable', 'string', 'max:100', 'unique:product_variants,sku'],

            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'status' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            // TAMBAHAN: Validasi galeri gambar
            'images' => ['nullable', 'array'],
            'images.*.url' => ['required_with:images', 'string', 'max:2048'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.sort_order' => ['nullable', 'integer'],

            // PERBAIKAN: Ubah dari 'attributes' menjadi 'attribute_values' agar sinkron dengan UseCase
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*.attribute_id' => ['required_with:attribute_values', 'integer', 'exists:product_attributes,id'],
            'attribute_values.*.value' => ['required_with:attribute_values', 'string', 'max:255'],

            'variants' => ['nullable', 'array'],
        ];
    }
}
