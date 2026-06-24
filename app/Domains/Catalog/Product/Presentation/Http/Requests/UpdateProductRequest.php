<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'seller_id' => ['nullable', 'uuid'],

            // HAPUS validasi 'sku' dari sini karena sudah pindah ke variants!

            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],

            // PERBAIKAN: Batasi status sesuai ENUM di database
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],

            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            // TAMBAHAN: Validasi untuk array images
            'images' => ['nullable', 'array'],
            'images.*.url' => ['required_with:images', 'string', 'max:2048'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.sort_order' => ['nullable', 'integer'],

            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['nullable', 'integer'],
            'attributes.*.value' => ['nullable', 'string'], // Pastikan tipe data jelas

            'variants' => ['nullable', 'array'],
            // Jika memungkinkan, tambahkan juga validasi detail variant di sini nanti
            // 'variants.*.sku' => ['required_with:variants', 'string', 'max:100'],
            // 'variants.*.price' => ['required_with:variants', 'numeric'],
        ];
    }
}
